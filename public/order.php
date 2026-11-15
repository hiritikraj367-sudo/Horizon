<?php
declare(strict_types=1);

require dirname(__DIR__) . '/private/store.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    jsonResponse(405, ['error' => 'Use POST to place an order.']);
}
startCheckout();
$token = $_SESSION['checkout_token'] ?? '';
session_write_close();
$input = static fn(string $key): string => is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';

if ($token === '' || !hash_equals($token, $input('token'))) {
    jsonResponse(403, ['error' => 'Checkout expired. Reload the page and try again.']);
}

$name = $input('name');
$phone = $input('phone');
$address = $input('address');
$city = $input('city');
$pincode = $input('pincode');
$payment = $input('payment');
$requestId = $input('request_id');
if ($name === '' || strlen($name) > 300 || $address === '' || strlen($address) > 1500
    || $city === '' || strlen($city) > 300 || !preg_match('/^[0-9]{10}$/D', $phone)
    || !preg_match('/^[0-9]{6}$/D', $pincode) || !preg_match('/^[a-f0-9]{32}$/D', $requestId)
    || !isset(PRICES[$payment])) {
    jsonResponse(422, ['error' => 'Check your delivery details, 10-digit mobile number and 6-digit PIN.']);
}
if ($payment === 'cashfree' && !cashfreeEnabled()) {
    jsonResponse(422, ['error' => 'Online payment is unavailable. Please choose cash on delivery.']);
}

try {
    $db = orderDatabase();
    $id = 'HV-' . bin2hex(random_bytes(12));
    $status = $payment === 'cod' ? 'cod_confirmed' : 'pending_payment';
    // Bind retries to the browser session so one checkout cannot retrieve another checkout's result.
    $requestKey = hash_hmac('sha256', $requestId, $token);
    $insert = $db->prepare('INSERT INTO orders
        (id, request_id, name, phone, address, city, pincode, product, quantity, payment, amount, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT(request_id) DO NOTHING');
    $insert->execute([$id, $requestKey, $name, $phone, $address, $city, $pincode,
        'Silicone scrubber set', 1, $payment, PRICES[$payment], $status]);

    $query = $db->prepare('SELECT * FROM orders WHERE request_id = ?');
    $query->execute([$requestKey]);
    $order = $query->fetch(PDO::FETCH_ASSOC);
    foreach (['name', 'phone', 'address', 'city', 'pincode', 'payment'] as $field) {
        if ($order[$field] !== $$field) {
            jsonResponse(409, ['error' => 'This checkout already has an order. Reload to start a new one.']);
        }
    }

    if ($payment === 'cod' || $order['status'] === 'paid') {
        jsonResponse(200, ['order_id' => $order['id'], 'status' => $order['status'], 'amount' => $order['amount']]);
    }

    $id = $order['id'];
    $session = $order['payment_session'];
    if (!$session) {
        $storeUrl = cashfreeSettings()['store_url'];
        try {
            $remote = cashfreeRequest('/orders', [
                'order_id' => $id, 'order_currency' => 'INR', 'order_amount' => $order['amount'],
                'customer_details' => ['customer_id' => $id, 'customer_name' => $name, 'customer_phone' => $phone],
                'order_meta' => [
                    'return_url' => $storeUrl . '/payment.php?order_id={order_id}',
                    'notify_url' => $storeUrl . '/webhook.php',
                ],
            ]);
        } catch (RuntimeException) {
            // A network retry may come after the gateway already created this order.
            $remote = cashfreeRequest('/orders/' . rawurlencode($id));
        }
        $session = $remote['payment_session_id'] ?? '';
        if (!is_string($session) || $session === '') {
            throw new RuntimeException('Cashfree session missing');
        }
        $update = $db->prepare('UPDATE orders SET payment_session = ? WHERE id = ?');
        $update->execute([$session, $id]);
    }
    jsonResponse(200, ['order_id' => $id, 'status' => 'pending_payment', 'session' => $session, 'mode' => cashfreeMode()]);
} catch (Throwable $e) {
    error_log('Home Vexa checkout failed: ' . get_class($e));
    jsonResponse(503, ['error' => 'Checkout could not complete. Please try again.']);
}
