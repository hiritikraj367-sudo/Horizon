<?php
declare(strict_types=1);
function cashfreeConfig(): array {
    $path = __DIR__ . '/data/cashfree.json';
    if (!is_readable($path)) throw new RuntimeException('Cashfree configuration missing at private/data/cashfree.json');
    $c = json_decode((string) file_get_contents($path), true);
    if (!is_array($c) || !in_array($c['environment'] ?? null, ['production', 'sandbox'], true)
        || !is_string($c['app_id'] ?? null) || ($c['app_id'] ?? '') === ''
        || !is_string($c['secret_key'] ?? null) || ($c['secret_key'] ?? '') === '') {
        throw new RuntimeException('Invalid Cashfree configuration (expected environment=production or sandbox, app_id, secret_key)');
    }
    return $c;
}
function cashfreeEnvironment(): string {
    return cashfreeConfig()['environment'];
}
function cashfreeBaseUrl(string $environment): string {
    if ($environment === 'sandbox') return 'https://sandbox.cashfree.com/pg/';
    if ($environment === 'production') return 'https://api.cashfree.com/pg/';
    throw new RuntimeException('Unknown Cashfree environment');
}
function cashfreeRequest(string $method, string $path, ?array $payload = null): array {
    $c = cashfreeConfig();
    $ch = curl_init(cashfreeBaseUrl($c['environment']) . ltrim($path, '/'));
    if ($ch === false) throw new RuntimeException('Cashfree cURL initialization failed');
    $headers = ['Content-Type: application/json','Accept: application/json',
        'x-api-version: 2025-01-01','x-client-id: ' . $c['app_id'],
        'x-client-secret: ' . $c['secret_key']];
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,
        CURLOPT_HTTPHEADER=>$headers,CURLOPT_TIMEOUT=>20,CURLOPT_CONNECTTIMEOUT=>8]);
    if ($payload !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = $raw === false ? curl_error($ch) : '';
    curl_close($ch);
    $data = is_string($raw) ? json_decode($raw, true) : null;
    if ($status < 200 || $status >= 300 || !is_array($data)) {
        $providerCode = is_array($data) && is_string($data['code'] ?? null) ? substr($data['code'], 0, 80) : 'unknown';
        error_log('Cashfree API request failed: HTTP ' . $status . ', provider code ' . $providerCode . ($curlError !== '' ? ', transport error ' . $curlError : ''));
        throw new RuntimeException('Cashfree request failed (HTTP ' . $status . ', code ' . $providerCode . ')');
    }
    return $data;
}
