<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
$id = is_string($_GET['order_id'] ?? null) ? $_GET['order_id'] : '';
$order = null;
$message = 'A confirmed order could not be found.';
try {
    $candidate = orderById($id);
    if ($candidate && in_array($candidate['status'], ['cod_confirmed', 'paid'], true)) {
        $order = $candidate;
        $message = $order['status'] === 'cod_confirmed'
            ? 'Your order is confirmed. You can pay when it arrives.'
            : 'Your payment is confirmed and your order has been placed.';
    } else {
        http_response_code(404);
    }
} catch (Throwable $e) {
    http_response_code(503);
    $message = 'Order confirmation is temporarily unavailable. Refresh this page to retry.';
}

$title = $order ? 'Thank you for your order!' : 'Order confirmation';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escape($title) ?> | Home Vexa</title>
    <link rel="stylesheet" href="./assets/checkout.css">
</head>
<body class="order-result">
    <main>
        <h1><?= escape($title) ?></h1>
        <p><?= escape($message) ?></p>
        <?php if ($order): ?>
            <p>Order reference <strong><?= escape($order['id']) ?></strong></p>
        <?php endif ?>
        <a href="./">Back to the store</a>
    </main>
</body>
</html>
