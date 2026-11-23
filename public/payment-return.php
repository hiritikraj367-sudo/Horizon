<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');
$id = is_string($_GET['order_id'] ?? null) ? $_GET['order_id'] : '';
$access = is_string($_GET['access'] ?? null) ? $_GET['access'] : '';
$message = 'We could not verify your payment yet. Please contact support with your order ID.';
try {
    $order = orderById($id);
    if (!$order || $order['payment'] !== 'cashfree' || !hash_equals(orderConfirmationToken($order), $access)) {
        http_response_code(404);
        throw new RuntimeException('Unknown payment reference');
    }
    $payment = cashfreeRequest('GET', 'orders/' . rawurlencode($id));
    if (($payment['order_status'] ?? '') === 'PAID'
        && ($payment['order_id'] ?? '') === $id
        && ($payment['order_currency'] ?? '') === 'INR'
        && abs((float) ($payment['order_amount'] ?? 0) - (float) $order['amount']) < 0.001) {
        $stmt = orderDatabase()->prepare("UPDATE orders SET status = 'paid', paid_at = COALESCE(paid_at, UTC_TIMESTAMP()) WHERE id = ? AND payment = 'cashfree' AND status = 'payment_pending'");
        $stmt->execute([$id]);
        header('Location: ./thank-you.php?' . http_build_query(['order_id' => $id, 'access' => $access]), true, 303);
        exit;
    }
    $message = 'Your payment is not confirmed yet. If money was deducted, please wait and contact support before paying again.';
} catch (Throwable $e) {
    error_log('Cashfree return verification failed: ' . get_class($e));
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Payment status | Home Vexa</title></head>
<body style="font:16px/1.6 Arial,sans-serif;max-width:600px;margin:60px auto;padding:20px;color:#344536">
<h1>Payment status</h1><p><?= escape($message) ?></p><p>Order reference: <?= escape($id) ?></p><a href="./">Return to Home Vexa</a></body></html>
