<?php
declare(strict_types=1);
function cashfreeConfig(): array {
    $path = __DIR__ . '/data/cashfree.json';
    if (!is_readable($path)) throw new RuntimeException('Cashfree configuration missing');
    $c = json_decode((string) file_get_contents($path), true);
    if (!is_array($c) || ($c['environment'] ?? '') !== 'production'
        || !is_string($c['app_id'] ?? null) || ($c['app_id'] ?? '') === ''
        || !is_string($c['secret_key'] ?? null) || ($c['secret_key'] ?? '') === '') {
        throw new RuntimeException('Invalid Cashfree production configuration');
    }
    return $c;
}
function cashfreeRequest(string $method, string $path, ?array $payload = null): array {
    $c = cashfreeConfig();
    $ch = curl_init('https://api.cashfree.com/pg/' . ltrim($path, '/'));
    $headers = ['Content-Type: application/json','Accept: application/json',
        'x-api-version: 2025-01-01','x-client-id: ' . $c['app_id'],
        'x-client-secret: ' . $c['secret_key']];
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,
        CURLOPT_HTTPHEADER=>$headers,CURLOPT_TIMEOUT=>20,CURLOPT_CONNECTTIMEOUT=>8]);
    if ($payload !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
    $raw = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = is_string($raw) ? json_decode($raw, true) : null;
    if ($status < 200 || $status >= 300 || !is_array($data)) {
        error_log('Cashfree API request failed: HTTP ' . $status);
        throw new RuntimeException('Payment provider unavailable. Please try again.');
    }
    return $data;
}
