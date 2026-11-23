<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
$stylesheet = file_get_contents(dirname(__DIR__) . '/assets/admin.css');
$styleNonce = base64_encode(random_bytes(18));
header("Content-Security-Policy: default-src 'none'; style-src 'nonce-$styleNonce'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
session_name('homevexa_admin');
startCheckout();

$passwordHash = adminPasswordHash();
$configured = password_get_info($passwordHash)['algo'] !== null;
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$authMarker = hash('sha256', $passwordHash);
$authenticated = $configured
    && hash_equals($authMarker, $_SESSION['auth_marker'] ?? '')
    && ($_SESSION['last_seen'] ?? 0) > time() - 1800;

if (!$authenticated) {
    unset($_SESSION['auth_marker'], $_SESSION['last_seen']);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    $action = $_POST['action'] ?? '';
    $password = $_POST['password'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(403);
        $error = 'The form expired. Please try again.';
    } elseif ($action === 'logout') {
        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: ./', true, 303);
        exit;
    } elseif ($action === 'login' && $configured && !$authenticated) {
        if (is_string($password) && strlen($password) <= 1024 && password_verify($password, $passwordHash)) {
            if (!session_regenerate_id(true)) {
                http_response_code(503);
                exit('Sign-in is temporarily unavailable.');
            }
            $_SESSION['auth_marker'] = $authMarker;
            $_SESSION['last_seen'] = time();
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            header('Location: ./', true, 303);
            exit;
        }
        usleep(400000);
        http_response_code(401);
        $error = 'Incorrect admin password.';
    }
}

$csrf = $_SESSION['csrf'];
if ($authenticated) {
    $_SESSION['last_seen'] = time();
}
session_write_close();

$statuses = ['cod_confirmed' => 'COD confirmed', 'pending_payment' => 'Payment pending', 'paid' => 'Paid'];
$filter = is_string($_GET['status'] ?? null) && isset($statuses[$_GET['status']]) ? $_GET['status'] : '';
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
$perPage = 25;
$orders = [];
$pageCount = 1;
$total = 0;

if ($authenticated) {
    try {
        $db = orderDatabase();
        $where = $filter === '' ? '' : ' WHERE status = :status';
        $count = $db->prepare('SELECT COUNT(*) FROM orders' . $where);
        $count->execute($filter === '' ? [] : ['status' => $filter]);
        $total = (int) $count->fetchColumn();
        $pageCount = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pageCount);
        $query = $db->prepare('SELECT id, name, phone, address, city, pincode, product, quantity,
            payment, amount, status, created_at FROM orders' . $where .
            ' ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset');
        if ($filter !== '') {
            $query->bindValue('status', $filter);
        }
        $query->bindValue('limit', $perPage, PDO::PARAM_INT);
        $query->bindValue('offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $query->execute();
        $orders = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        http_response_code(503);
        error_log('Home Vexa admin could not load orders: ' . get_class($e));
        $error = 'Orders could not load. Try again shortly.';
    }
}

function pageUrl(int $page, string $status): string
{
    return './?' . http_build_query(['page' => $page, 'status' => $status]);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orders | Home Vexa Admin</title>
    <style nonce="<?= $styleNonce ?>"><?= $stylesheet ?></style>
</head>
<body>
<main class="<?= $authenticated ? 'dashboard' : 'sign-in' ?>">
    <header>
        <div>
            <p class="brand">Home Vexa</p>
            <h1><?= $authenticated ? 'Orders' : 'Admin sign-in' ?></h1>
        </div>
    <?php if ($authenticated): ?>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= escape($csrf) ?>">
            <button name="action" value="logout">Sign out</button>
        </form>
    <?php endif ?>
    </header>

    <?php if ($error !== ''): ?><p class="error" role="alert"><?= escape($error) ?></p><?php endif ?>
    <?php if (!$configured): ?>
        <p>Admin sign-in is unavailable until a password is configured on the server.</p>
    <?php elseif (!$authenticated): ?>
        <form method="post" class="login-form">
            <input type="hidden" name="csrf" value="<?= escape($csrf) ?>">
            <label for="admin-password">Admin password</label>
            <input id="admin-password" type="password" name="password" autocomplete="current-password" required>
            <button name="action" value="login">Sign in</button>
        </form>
    <?php else: ?>
        <p><a href="./landing-pages/">Manage Landing Pages →</a></p>
        <form method="get" class="filters">
            <label for="status">Payment status</label>
            <select name="status" id="status">
                <option value="">All statuses</option>
                <?php foreach ($statuses as $value => $label): ?>
                    <option value="<?= escape($value) ?>" <?= $filter === $value ? 'selected' : '' ?>><?= escape($label) ?></option>
                <?php endforeach ?>
            </select>
            <button type="submit">Filter</button>
        </form>
        <?php if ($error === '' && !$orders): ?>
            <p>No orders found.</p>
        <?php elseif ($orders): ?>
            <p><?= $total ?> <?= $total === 1 ? 'order' : 'orders' ?> · Times in UTC</p>
            <div class="table-scroll" role="region" aria-label="Orders table" tabindex="0">
                <table>
                    <thead><tr><th>Order</th><th>Customer</th><th>Delivery address</th><th>Payment</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong><?= escape($order['id']) ?></strong><small><?= escape($order['created_at']) ?></small><small><?= (int) $order['quantity'] ?> × <?= escape($order['product']) ?></small></td>
                            <td><?= escape($order['name']) ?><small><a href="tel:<?= escape($order['phone']) ?>"><?= escape($order['phone']) ?></a></small></td>
                            <td><span class="address"><?= escape($order['address']) ?></span><small><?= escape($order['city']) ?> <?= escape($order['pincode']) ?></small></td>
                            <td><?= $order['payment'] === 'cod' ? 'Cash on delivery' : 'Online payment (historical)' ?></td>
                            <td>₹<?= (int) $order['amount'] ?></td>
                            <td><?= escape($statuses[$order['status']] ?? $order['status']) ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <?php if ($pageCount > 1): ?>
                <nav class="pagination" aria-label="Order pages">
                    <?php if ($page > 1): ?><a href="<?= escape(pageUrl($page - 1, $filter)) ?>">Previous</a><?php endif ?>
                    <span>Page <?= $page ?> of <?= $pageCount ?></span>
                    <?php if ($page < $pageCount): ?><a href="<?= escape(pageUrl($page + 1, $filter)) ?>">Next</a><?php endif ?>
                </nav>
            <?php endif ?>
        <?php endif ?>
    <?php endif ?>
</main>
</body>
</html>
