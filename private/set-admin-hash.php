<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/store.php';

$hash = $argv[1] ?? '';
if (strlen($hash) > 512 || password_get_info($hash)['algo'] === null) {
    fwrite(STDERR, "Pass a valid PHP password hash as the only argument.\n");
    exit(1);
}
if (getenv('ADMIN_PASSWORD_HASH')) {
    fwrite(STDERR, "Remove the ADMIN_PASSWORD_HASH environment override before setting the MySQL password.\n");
    exit(1);
}

try {
    $statement = orderDatabase()->prepare('INSERT INTO admin_credentials (id, password_hash)
        VALUES (1, ?) ON DUPLICATE KEY UPDATE password_hash = ?');
    $statement->execute([$hash, $hash]);
    echo "Admin password configured in MySQL.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Could not save the MySQL admin credential. Check the database path and permissions.\n");
    exit(1);
}
