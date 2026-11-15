<?php
declare(strict_types=1);

require dirname(__DIR__) . '/private/store.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !cashfreeEnabled()) {
    jsonResponse(404, ['error' => 'Webhook unavailable']);
}
$raw = file_get_contents('php://input');
$timestamp = $_SERVER['HTTP_X_WEBHOOK_TIMESTAMP'] ?? '';
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
$expected = base64_encode(hash_hmac('sha256', $timestamp . $raw, getenv('CASHFREE_CLIENT_SECRET'), true));
if ($timestamp === '' || !hash_equals($expected, $signature)) {
    jsonResponse(401, ['error' => 'Invalid signature']);
}

try {
    $event = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    $id = $event['data']['order']['order_id'] ?? null;
    if (($event['type'] ?? null) === 'PAYMENT_SUCCESS_WEBHOOK' && is_string($id)) {
        $order = orderById($id);
        if ($order && $order['payment'] === 'cashfree' && !verifyCashfreeOrder($order)) {
            jsonResponse(503, ['error' => 'Payment has not been verified']);
        }
    }
    jsonResponse(200, ['received' => true]);
} catch (JsonException) {
    jsonResponse(400, ['error' => 'Invalid JSON']);
} catch (Throwable) {
    jsonResponse(503, ['error' => 'Unable to verify payment']);
}
