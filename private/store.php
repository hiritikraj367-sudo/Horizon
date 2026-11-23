<?php
declare(strict_types=1);

require_once __DIR__ . '/order-confirmation-email.php';
require_once __DIR__ . '/cashfree.php';

const COD_PRICE = 239;

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

function orderById(string $id): ?array
{
    if (!preg_match('/^HV-[a-f0-9]{24}$/D', $id)) {
        return null;
    }
    $query = orderDatabase()->prepare('SELECT * FROM orders WHERE id = ?');
    $query->execute([$id]);
    return $query->fetch(PDO::FETCH_ASSOC) ?: null;
}

function orderConfirmationToken(array $order): string
{
    return hash_hmac('sha256', 'confirmation:' . $order['id'], $order['request_id']);
}

function orderDeliveryWindow(array $order): string
{
    $date = new DateTimeImmutable($order['created_at'], new DateTimeZone('UTC'));
    $local = $date->setTimezone(new DateTimeZone('Asia/Kolkata'));
    return $local->modify('+3 days')->format('j M Y') . ' – ' . $local->modify('+5 days')->format('j M Y');
}

function sendOrderConfirmation(string $email, array $order): void
{
    $reference = $order['id'];
    $sender = 'orders@homevexa.online';
    $headers = [
        'From' => 'Home Vexa <' . $sender . '>',
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/html; charset=UTF-8',
        'Content-Transfer-Encoding' => 'quoted-printable',
    ];

    try {
        if (!function_exists('mail')) {
            error_log('Home Vexa confirmation mail unavailable: PHP mail() is disabled for order ' . $reference);
            return;
        }
        $url = 'https://homevexa.online/thank-you.php?' . http_build_query([
            'order_id' => $reference,
            'access' => orderConfirmationToken($order),
        ]);
        $message = quoted_printable_encode(renderOrderConfirmationEmail($order, orderDeliveryWindow($order), $url));
        if (@mail($email, 'Home Vexa order confirmation', $message, $headers, '-f' . $sender)) {
            error_log('Home Vexa confirmation mail accepted by local mailer for order ' . $reference);
            return;
        }
    } catch (Throwable $e) {
        error_log('Home Vexa confirmation mail error: ' . get_class($e));
    }

    error_log('Home Vexa confirmation mail could not be queued for order ' . $reference);
}

function jsonResponse(int $status, array $body): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body, JSON_THROW_ON_ERROR);
    exit;
}
