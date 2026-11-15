<?php
declare(strict_types=1);

require dirname(__DIR__) . '/private/store.php';

header('Cache-Control: no-store');
$id = is_string($_GET['order_id'] ?? null) ? $_GET['order_id'] : '';
$message = 'This order could not be found.';
try {
    $order = orderById($id);
    if ($order && $order['payment'] === 'cashfree') {
        $message = verifyCashfreeOrder($order)
            ? 'Payment verified. Your order is confirmed.'
            : 'Payment has not been confirmed. Refresh this page to check again.';
    } else {
        http_response_code(404);
    }
} catch (Throwable $e) {
    http_response_code(503);
    $message = 'Payment could not be verified right now. Refresh this page to retry.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order status | Home Vexa</title>
    <link rel="stylesheet" href="./assets/checkout.css">
</head>
<body class="order-result">
    <main>
        <h1>Order status</h1>
        <p><?= escape($message) ?></p>
        <?php if (!empty($order)): ?><p>Reference <strong><?= escape($order['id']) ?></strong></p><?php endif ?>
        <a href="./">Back to the store</a>
    </main>
</body>
</html>
