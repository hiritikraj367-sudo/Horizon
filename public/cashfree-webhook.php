<?php
declare(strict_types=1);

// Cashfree server-to-server notification. Do not trust POSTed payment statuses.
// Verify the HMAC, then re-check the order using authenticated Cashfree GET API.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
require __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    jsonResponse(405, ['error' => 'Method not allowed']);
}

$raw = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
$timestamp = $_SERVER['HTTP_X_WEBHOOK_TIMESTAMP'] ?? '';
if (!is_string($raw) || strlen($raw) > 1024 * 1024 || !is_string($signature)
    || !is_string($timestamp) || !preg_match('/^[0-9]{10,13}$/D', $timestamp)) {
    jsonResponse(400, ['error' => 'Invalid webhook request']);
}

try {
    $config = cashfreeConfig();
    $expected = base64_encode(hash_hmac('sha256', $timestamp . $raw, $config['secret_key'], true));
    if (!hash_equals($expected, $signature)) {
        jsonResponse(401, ['error' => 'Invalid webhook signature']);
    }
    $event = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($event)) {
        jsonResponse(400, ['error' => 'Invalid webhook event']);
    }
    if (($event['type'] ?? '') !== 'PAYMENT_SUCCESS_WEBHOOK'
        || ($event['data']['payment']['payment_status'] ?? '') !== 'SUCCESS') {
        jsonResponse(200, ['ok' => true, 'ignored' => true]);
    }

    $orderId = $event['data']['order']['order_id'] ?? '';
    if (!is_string($orderId) || !preg_match('/^HV-[a-f0-9]{24}$/D', $orderId)) {
        jsonResponse(400, ['error' => 'Invalid order reference']);
    }

    $order = orderById($orderId);
    if (!$order) {
        // A valid Cashfree event can arrive for an order that is not in our store.
        error_log('Cashfree webhook ignored unknown order');
        jsonResponse(200, ['ok' => true, 'ignored' => true]);
    }
    $isTest = $config['environment'] === 'sandbox';
    $paymentType = $isTest ? 'cashfree_test' : 'cashfree';
    $pendingStatus = $isTest ? 'test_pending' : 'payment_pending';
    $paidStatus = $isTest ? 'test_paid' : 'paid';
    if ($order['payment'] !== $paymentType) {
        error_log('Cashfree webhook environment/order mismatch');
        jsonResponse(200, ['ok' => true, 'ignored' => true]);
    }
    if ($order['status'] === $paidStatus) {
        jsonResponse(200, ['ok' => true]);
    }
    if ($order['status'] !== $pendingStatus) {
        jsonResponse(200, ['ok' => true, 'ignored' => true]);
    }

    // Signed event is necessary but not enough: verify status, identity and amount
    // with Cashfree, so a forged or malformed body cannot mark an order paid.
    $provider = cashfreeRequest('GET', 'orders/' . rawurlencode($orderId));
    if (($provider['order_status'] ?? '') !== 'PAID'
        || ($provider['order_id'] ?? '') !== $orderId
        || ($provider['order_currency'] ?? '') !== 'INR'
        || abs((float) ($provider['order_amount'] ?? 0) - (float) $order['amount']) >= 0.001) {
        error_log('Cashfree webhook payment verification pending or mismatched');
        jsonResponse(503, ['error' => 'Payment verification pending']);
    }

    if ($isTest) {
        $stmt = orderDatabase()->prepare(
            "UPDATE orders SET status = 'test_paid'
             WHERE id = ? AND payment = 'cashfree_test' AND status = 'test_pending'"
        );
    } else {
        $stmt = orderDatabase()->prepare(
            "UPDATE orders SET status = 'paid', paid_at = COALESCE(paid_at, UTC_TIMESTAMP())
             WHERE id = ? AND payment = 'cashfree' AND status = 'payment_pending'"
        );
    }
    $stmt->execute([$orderId]);
    jsonResponse(200, ['ok' => true]);
} catch (JsonException $e) {
    jsonResponse(400, ['error' => 'Invalid webhook JSON']);
} catch (Throwable $e) {
    $traceId = bin2hex(random_bytes(6));
    error_log('Cashfree webhook trace=' . $traceId . ' error=' . get_class($e) . ' message=' . $e->getMessage());
    jsonResponse(503, ['error' => 'Webhook temporarily unavailable', 'trace_id' => $traceId]);
}
