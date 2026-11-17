<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

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
$email = $input('email');
if ($email !== '' && (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
    jsonResponse(422, ['error' => 'Enter a valid email address or leave the email field blank.']);
}
$address = $input('address');
$city = $input('city');
$pincode = $input('pincode');
$payment = $input('payment') ?: 'cod';
$requestId = $input('request_id');
if ($name === '' || strlen($name) > 300 || $address === '' || strlen($address) > 1500
    || $city === '' || strlen($city) > 300 || !preg_match('/^[0-9]{10}$/D', $phone)
    || !preg_match('/^[0-9]{6}$/D', $pincode) || !preg_match('/^[a-f0-9]{32}$/D', $requestId)
    || $payment !== 'cod') {
    jsonResponse(422, ['error' => 'Check your delivery details, 10-digit mobile number and 6-digit PIN.']);
}
try {
    $stage = 'database_connection';
    $db = orderDatabase();
    $stage = 'order_save';
    $id = 'HV-' . bin2hex(random_bytes(12));
    $status = 'cod_confirmed';
    // Bind retries to the browser session so one checkout cannot retrieve another checkout's result.
    $requestKey = hash_hmac('sha256', $requestId, $token);
    $insert = $db->prepare('INSERT INTO orders
        (id, request_id, name, phone, address, city, pincode, product, quantity, payment, amount, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE request_id = request_id');
    $insert->execute([$id, $requestKey, $name, $phone, $address, $city, $pincode,
        'Silicone scrubber set', 1, $payment, COD_PRICE, $status]);

    $query = $db->prepare('SELECT * FROM orders WHERE request_id = ?');
    $query->execute([$requestKey]);
    $order = $query->fetch(PDO::FETCH_ASSOC);
    if (!$order) {
        throw new RuntimeException('Order could not be retrieved');
    }
    foreach (['name', 'phone', 'address', 'city', 'pincode', 'payment'] as $field) {
        if ($order[$field] !== $$field) {
            jsonResponse(409, ['error' => 'This checkout already has an order. Reload to start a new one.']);
        }
    }

    // Only the request that inserted this order sends the confirmation.
    if ($email !== '' && $order['id'] === $id) {
        sendOrderConfirmation($email, $order);
    }

    jsonResponse(200, ['order_id' => $order['id'], 'status' => $order['status'], 'amount' => $order['amount']]);
} catch (Throwable $e) {
    $diagnostic = ['stage' => $stage];
    error_log('Home Vexa checkout failed at ' . $stage . ': ' . get_class($e));
    jsonResponse(503, [
        'error' => 'Checkout could not complete. Please try again.',
        'diagnostic' => $diagnostic,
    ]);
}
