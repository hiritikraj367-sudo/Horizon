<?php
declare(strict_types=1);
// Run only from SSH/Terminal: php /home/homevexa/homevexa-private/test-cashfree.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
require __DIR__ . '/cashfree.php';
echo "Cashfree connectivity diagnostic (no charge / no order created)\n";
try {
    $config = cashfreeConfig();
    echo "Config: OK (" . $config['environment'] . ")\n";
    echo "cURL: " . (function_exists('curl_init') ? 'available' : 'MISSING') . "\n";
    if (!function_exists('curl_init')) exit(2);
    // A deliberately nonexistent order tests authenticated API connectivity without creating a payment.
    $ch = curl_init(cashfreeBaseUrl($config['environment']) . 'orders/HV-DIAGNOSTIC-NONEXISTENT');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json', 'x-api-version: 2025-01-01',
            'x-client-id: ' . $config['app_id'], 'x-client-secret: ' . $config['secret_key'],
        ],
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $transport = $raw === false ? curl_error($ch) : '';
    curl_close($ch);
    $body = is_string($raw) ? json_decode($raw, true) : null;
    $code = is_array($body) && is_string($body['code'] ?? null) ? substr($body['code'], 0, 80) : 'unknown';
    echo "HTTP status: $status\nProvider code: $code\n";
    if ($transport !== '') echo "Transport: " . preg_replace('/[^a-zA-Z0-9 .:_-]/', '', $transport) . "\n";
    if ($status === 404) echo "Result: API reached; nonexistent test order not found (expected).\n";
    elseif ($status === 401 || $status === 403) echo "Result: Authentication or account permission issue.\n";
    elseif ($status === 0) echo "Result: Connection failed; check DNS, TLS, firewall or cURL.\n";
    else echo "Result: Review HTTP status and provider code.\n";
} catch (Throwable $e) {
    echo "Setup error: " . preg_replace('/[^a-zA-Z0-9 .:_()=\/-]/', '', $e->getMessage()) . "\n";
    exit(1);
}
