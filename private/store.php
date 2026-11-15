<?php
declare(strict_types=1);

require_once __DIR__ . '/cashfree-settings.php';

const PRICES = ['cod' => 239, 'cashfree' => 200];

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function startCheckout(): void
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_start(['use_strict_mode' => true, 'cookie_httponly' => true,
        'cookie_samesite' => 'Lax', 'cookie_secure' => $https]);
}

function adminPasswordHash(): string
{
    $configuredHash = getenv('ADMIN_PASSWORD_HASH');
    if ($configuredHash !== false && $configuredHash !== '') {
        return $configuredHash;
    }
    try {
        $hash = orderDatabase()->query('SELECT password_hash FROM admin_credentials WHERE id = 1')->fetchColumn();
        if (is_string($hash) && $hash !== '') {
            return $hash;
        }
    } catch (RuntimeException) {
        error_log('Home Vexa could not read the SQLite admin credential');
    }
    $path = __DIR__ . '/admin-password.hash';
    if (!is_readable($path)) {
        return '';
    }
    $hash = @file_get_contents($path, false, null, 0, 512);
    return is_string($hash) ? trim($hash) : '';
}

function orderDatabase(): PDO
{
    static $db;
    if ($db instanceof PDO) {
        return $db;
    }
    $path = getenv('ORDER_DB_PATH') ?: __DIR__ . '/data/orders.sqlite';
    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0700, true) && !is_dir(dirname($path))) {
        throw new RuntimeException('Unable to initialize order storage');
    }
    $previousMask = umask(0077);
    try {
        $db = new PDO('sqlite:' . $path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $db->exec('PRAGMA busy_timeout = 5000');
        $db->exec('CREATE TABLE IF NOT EXISTS orders (
            id TEXT PRIMARY KEY, request_id TEXT NOT NULL UNIQUE, name TEXT NOT NULL,
            phone TEXT NOT NULL, address TEXT NOT NULL, city TEXT NOT NULL, pincode TEXT NOT NULL,
            product TEXT NOT NULL, quantity INTEGER NOT NULL, payment TEXT NOT NULL,
            amount INTEGER NOT NULL, status TEXT NOT NULL, payment_session TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, paid_at TEXT
        )');
        $db->exec('CREATE TABLE IF NOT EXISTS admin_credentials (
            id INTEGER PRIMARY KEY CHECK (id = 1), password_hash TEXT NOT NULL
        )');
    } finally {
        umask($previousMask);
    }
    return $db;
}

function cashfreeEnabled(): bool
{
    return (bool) (cashfreeSettings()['client_id'] && cashfreeSettings()['secret']
        && cashfreeStoreUrlValid(cashfreeSettings()['store_url'])
        && function_exists('curl_init'));
}

function cashfreeMode(): string
{
    return cashfreeSettings()['mode'];
}

function cashfreeRequest(string $endpoint, ?array $body = null): array
{
    if (!cashfreeEnabled()) {
        throw new RuntimeException('Cashfree configuration missing');
    }
    $base = cashfreeMode() === 'production' ? 'https://api.cashfree.com/pg' : 'https://sandbox.cashfree.com/pg';
    $curl = curl_init($base . $endpoint);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json', 'x-api-version: 2025-01-01',
            'x-client-id: ' . cashfreeSettings()['client_id'],
            'x-client-secret: ' . cashfreeSettings()['secret'],
        ],
    ]);
    if ($body !== null) {
        curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($body, JSON_THROW_ON_ERROR)]);
    }
    $raw = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    if (!is_string($raw) || $status < 200 || $status >= 300) {
        throw new RuntimeException('Cashfree request failed with status ' . $status);
    }
    return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
}

function orderById(string $id): ?array
{
    if (!preg_match('/^HV-[a-f0-9]{24}$/D', $id)) {
        return null;
    }
    $query = orderDatabase()->prepare('SELECT * FROM orders WHERE id = ?');
    $query->execute([$id]);
    return $query->fetch(PDO::FETCH_ASSOC) ?: null;
}

function verifyCashfreeOrder(array $order): bool
{
    if ($order['status'] === 'paid') {
        return true;
    }
    $remote = cashfreeRequest('/orders/' . rawurlencode($order['id']));
    if (($remote['order_status'] ?? null) !== 'PAID'
        || ($remote['order_id'] ?? null) !== $order['id']
        || ($remote['order_currency'] ?? null) !== 'INR'
        || (float) ($remote['order_amount'] ?? 0) !== (float) $order['amount']) {
        return false;
    }
    $query = orderDatabase()->prepare("UPDATE orders SET status = 'paid', paid_at = CURRENT_TIMESTAMP
        WHERE id = ? AND status = 'pending_payment'");
    $query->execute([$order['id']]);
    return true;
}

function jsonResponse(int $status, array $body): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body, JSON_THROW_ON_ERROR);
    exit;
}
