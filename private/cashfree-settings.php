<?php
declare(strict_types=1);

function cashfreeSettingsPath(): string
{
    $database = getenv('ORDER_DB_PATH') ?: __DIR__ . '/data/orders.sqlite';
    return dirname($database) . '/cashfree-settings.json';
}

function cashfreeSettings(): array
{
    static $settings;
    if ($settings !== null) {
        return $settings;
    }
    $defaults = [
        'client_id' => getenv('CASHFREE_CLIENT_ID') ?: '',
        'secret' => getenv('CASHFREE_CLIENT_SECRET') ?: '',
        'mode' => getenv('CASHFREE_MODE') === 'production' ? 'production' : 'sandbox',
        'store_url' => rtrim(getenv('STORE_URL') ?: '', '/'),
    ];

    $path = cashfreeSettingsPath();
    if (!is_file($path)) {
        return $settings = $defaults;
    }
    $raw = file_get_contents($path);
    $stored = is_string($raw) ? json_decode($raw, true) : null;
    if (!is_array($stored) || array_keys($stored) !== array_keys($defaults) || count(array_filter($stored, 'is_string')) !== 4) {
        error_log('Home Vexa Cashfree settings could not be read');
        return $settings = ['client_id' => '', 'secret' => '', 'mode' => 'sandbox', 'store_url' => ''];
    }
    return $settings = $stored;
}

function cashfreeStoreUrlValid(string $url): bool
{
    if (strlen($url) > 512 || !filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    $parts = parse_url($url);
    return ($parts['scheme'] ?? null) === 'https' && isset($parts['host'])
        && !isset($parts['user']) && !isset($parts['pass']) && !isset($parts['query']) && !isset($parts['fragment']);
}

function saveCashfreeSettings(array $post): void
{
    $input = static fn(string $key): string => is_string($post[$key] ?? null) ? trim($post[$key]) : '';
    $current = cashfreeSettings();
    $mode = $input('mode');
    $url = rtrim($input('store_url'), '/');
    if (!in_array($mode, ['sandbox', 'production'], true) || !cashfreeStoreUrlValid($url)) {
        throw new InvalidArgumentException('Choose a mode and enter your storefront’s HTTPS URL.');
    }
    $id = $input('client_id');
    $secret = $input('client_secret');
    if ($mode === $current['mode']) {
        $id = $id !== '' ? $id : $current['client_id'];
        $secret = $secret !== '' ? $secret : $current['secret'];
    }
    foreach ([$id, $secret] as $credential) {
        if (!preg_match('/^[!-~]{1,512}$/D', $credential)) {
            throw new InvalidArgumentException('Enter both the Cashfree App ID and Secret Key for the selected mode.');
        }
    }

    $path = cashfreeSettingsPath();
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to initialize private settings storage');
    }
    $temporary = tempnam($directory, '.cashfree-');
    if ($temporary === false) {
        throw new RuntimeException('Unable to create a private settings file');
    }
    try {
        $json = json_encode(['client_id' => $id, 'secret' => $secret, 'mode' => $mode, 'store_url' => $url], JSON_THROW_ON_ERROR);
        if (!chmod($temporary, 0600) || file_put_contents($temporary, $json) !== strlen($json)
            || !rename($temporary, $path)) {
            throw new RuntimeException('Unable to save Cashfree settings');
        }
    } finally {
        if (is_file($temporary)) {
            unlink($temporary);
        }
    }
}
