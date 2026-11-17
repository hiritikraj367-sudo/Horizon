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
        error_log('Home Vexa could not read the MySQL admin credential');
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
    $path = __DIR__ . '/database.json';
    if (!is_readable($path)) {
        throw new RuntimeException('MySQL configuration is missing');
    }
    $raw = file_get_contents($path);
    $config = is_string($raw) ? json_decode($raw, true) : null;
    $host = $config['host'] ?? 'localhost';
    $name = $config['database'] ?? '';
    $user = $config['username'] ?? '';
    $password = $config['password'] ?? '';
    if (!is_array($config) || !is_string($host) || !preg_match('/^[a-zA-Z0-9.-]+$/D', $host)
        || !is_string($name) || !preg_match('/^[a-zA-Z0-9_$-]+$/D', $name)
        || !is_string($user) || $user === '' || !is_string($password) || $password === '') {
        throw new RuntimeException('MySQL configuration is invalid');
    }

    $connection = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $connection->exec("SET time_zone = '+00:00'");
    $connection->exec('CREATE TABLE IF NOT EXISTS orders (
        id VARCHAR(32) PRIMARY KEY,
        request_id VARCHAR(64) NOT NULL UNIQUE,
        name VARCHAR(300) NOT NULL,
        phone CHAR(10) NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(300) NOT NULL,
        pincode CHAR(6) NOT NULL,
        product VARCHAR(150) NOT NULL,
        quantity INT UNSIGNED NOT NULL,
        payment VARCHAR(20) NOT NULL,
        amount INT UNSIGNED NOT NULL,
        status VARCHAR(32) NOT NULL,
        payment_session TEXT,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        paid_at DATETIME DEFAULT NULL,
        INDEX orders_created (created_at, id),
        INDEX orders_status_created (status, created_at, id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $connection->exec('CREATE TABLE IF NOT EXISTS admin_credentials (
        id TINYINT UNSIGNED PRIMARY KEY,
        password_hash VARCHAR(512) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    return $db = $connection;
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

final class CashfreeRequestException extends RuntimeException
{
    public function __construct(
        public readonly int $httpStatus,
        public readonly int $curlNumber,
        public readonly string $gatewayCode
    ) {
        parent::__construct('Cashfree request failed with status ' . $httpStatus);
    }
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
    $curlError = curl_errno($curl);
    curl_close($curl);
    if (!is_string($raw) || $status < 200 || $status >= 300) {
        $details = is_string($raw) ? json_decode($raw, true) : null;
        $code = $details['code'] ?? '';
        $safeCode = is_string($code) && preg_match('/^[a-zA-Z0-9_-]{1,80}$/D', $code) ? $code : 'unavailable';
        error_log(sprintf(
            'Home Vexa Cashfree %s %s failed: HTTP %d, cURL %d, code %s',
            cashfreeMode(),
            $body === null ? 'get order' : 'create order',
            $status,
            $curlError,
            $safeCode
        ));
        $message = $details['message'] ?? null;
        if ($body !== null && is_string($message) && $message !== '') {
            $settings = cashfreeSettings();
            $sensitiveValues = array_filter([
                $settings['client_id'],
                $settings['secret'],
                $body['customer_details']['customer_name'] ?? '',
                $body['customer_details']['customer_phone'] ?? '',
            ], static fn(string $value): bool => $value !== '');
            $message = str_ireplace($sensitiveValues, '[redacted]', $message);
            $message = preg_replace('/[[:cntrl:]]+/', ' ', $message) ?? '';
            error_log('Home Vexa Cashfree create order message: ' . substr($message, 0, 350));
        }
        throw new CashfreeRequestException($status, $curlError, $safeCode);
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
