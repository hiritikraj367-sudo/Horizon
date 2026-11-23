<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');
$id = is_string($_GET['order_id'] ?? null) ? $_GET['order_id'] : '';
$access = is_string($_GET['access'] ?? null) ? $_GET['access'] : '';
$order = null;
$showDeliveryAddress = false;
$deliveryWindow = '';
$message = 'A confirmed order could not be found.';
try {
    $candidate = orderById($id);
    if ($candidate && in_array($candidate['status'], ['cod_confirmed', 'paid', 'test_paid'], true)) {
        $deliveryWindow = orderDeliveryWindow($candidate);
        $showDeliveryAddress = hash_equals(orderConfirmationToken($candidate), $access);
        $order = $candidate;
        $message = $order['status'] === 'test_paid'
            ? 'Cashfree sandbox test successful. No real money was charged. This is NOT a real order and will not be shipped.'
            : ($order['status'] === 'cod_confirmed'
                ? 'Your order is confirmed. You can pay when it arrives.'
                : 'Your payment is confirmed and your order has been placed.');
    } else {
        http_response_code(404);
    }
} catch (Throwable $e) {
    http_response_code(503);
    $message = 'Order confirmation is temporarily unavailable. Refresh this page to retry.';
}

$title = $order ? ($order['status'] === 'test_paid' ? 'Test payment completed' : 'Thank you for your order!') : 'Order confirmation';
$isTest = $order && $order['status'] === 'test_paid';
$isPaid = $order && $order['status'] === 'paid';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f5ef">
    <title><?= escape($title) ?> | Home Vexa</title>
    <link rel="icon" href="./assets/home-vexa-mark.svg" type="image/svg+xml">
    <link rel="stylesheet" href="./assets/store.css">
    <style>
        .he-confirmation { min-height: 100vh; margin: 0; background: #f7f5ef; color: #344536; font: 15px/1.6 Arial, Helvetica, sans-serif; }
        .he-confirmation-header { display: flex; align-items: center; min-height: 68px; padding: 12px max(5.2%, calc((100vw - 1280px) / 2)); background: #fbfaf5; border-bottom: 1px solid #e3e1d5; }
        .he-confirmation-header .he-brand-lockup { gap: 5px; text-decoration: none; }
        .he-confirmation-header .he-brand-tagline { padding-left: 39px; }
        .he-confirmation-main { width: min(100% - 32px, 560px); margin: clamp(28px, 7vw, 78px) auto; }
        .he-confirmation-card { padding: clamp(22px, 6vw, 44px); border: 1px solid #e3e1d5; border-radius: 16px; background: #fbfaf5; box-shadow: 0 14px 40px #3445360d; }
        .he-confirmation-icon { display: grid; width: 54px; height: 54px; margin: 0 0 20px; place-items: center; border-radius: 50%; background: #e9eee3; color: #3b503d; }
        .he-confirmation-card h1 { margin: 0; font: 400 clamp(32px, 7vw, 48px)/1.08 Georgia, 'Times New Roman', serif; letter-spacing: -.035em; }
        .he-confirmation-message { margin: 12px 0 0; color: #52604f; }
        .he-confirmation-details { display: grid; gap: 12px; margin: 24px 0 0; padding: 16px; border: 1px solid #dfe3d7; border-radius: 10px; background: #f0f2e9; }
        .he-confirmation-details > div { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 2px 12px; }
        .he-confirmation-details dt { color: #52604f; }
        .he-confirmation-details dd { min-width: 0; margin: 0; color: #344536; font-weight: 700; overflow-wrap: anywhere; }
        .he-confirmation-product { display: flex; align-items: center; gap: 16px; margin-top: 24px; }
        .he-confirmation-product img { width: 86px; height: 86px; flex: none; object-fit: cover; border-radius: 10px; }
        .he-confirmation-card h2 { margin: 0; font: 600 20px/1.25 Georgia, serif; }
        .he-confirmation-address { margin-top: 22px; }
        .he-confirmation-address address { margin-top: 8px; padding: 14px; border: 1px solid #dfe3d7; border-radius: 10px; background: #f0f2e9; font-style: normal; overflow-wrap: anywhere; }
        .he-confirmation-link { display: inline-flex; min-height: 46px; margin-top: 24px; padding: 0 18px; align-items: center; gap: 12px; border-radius: 7px; background: #3b503d; color: #fffdf7; font-weight: 700; text-decoration: none; }
        .he-confirmation-link:hover { background: #2f4231; color: #fffdf7; }
    </style>
</head>
<body class="he-confirmation">
    <header class="he-confirmation-header">
        <a class="he-brand-lockup" href="./" aria-label="Home Vexa home">
            <span class="he-brand-row">
                <img class="he-brand-symbol" src="./assets/home-vexa-mark.svg" alt="" width="32" height="32">
                <span class="he-wordmark">Home Vexa</span>
            </span>
            <span class="he-brand-tagline">EVERYDAY HOME ESSENTIALS</span>
        </a>
    </header>
    <main class="he-confirmation-main">
        <section class="he-confirmation-card" aria-labelledby="confirmation-title">
            <?php if ($order): ?>
                <span class="he-confirmation-icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m5 12 4 4L19 6"/>
                    </svg>
                </span>
            <?php endif ?>
            <h1 id="confirmation-title"><?= escape($title) ?></h1>
            <p class="he-confirmation-message"><?= escape($message) ?></p>
            <?php if ($order): ?>
                <div class="he-confirmation-product">
                    <img src="./assets/01.webp" alt="<?= escape($order['product']) ?>" width="86" height="86">
                    <div>
                        <h2><?= escape($order['product']) ?></h2>
                        <p>Quantity <?= (int) $order['quantity'] ?> · ₹<?= number_format((int) $order['amount']) ?> total</p>
                    </div>
                </div>
                <dl class="he-confirmation-details">
                    <div>
                        <dt>Order reference</dt>
                        <dd><?= escape($order['id']) ?></dd>
                    </div>
                    <div>
                        <dt>Payment</dt>
                        <dd><?= $isTest ? 'Sandbox only (no real charge)' : ($isPaid ? 'Paid online' : 'Cash on delivery') ?></dd>
                    </div>
                    <?php if (!$isTest): ?>
                    <div>
                        <dt>Estimated delivery</dt>
                        <dd><?= escape($deliveryWindow) ?> (3–5 days)</dd>
                    </div>
                    <?php endif ?>
                </dl>
                <?php if ($showDeliveryAddress && !$isTest): ?>
                    <div class="he-confirmation-address">
                        <h2>Delivery address</h2>
                        <address>
                            <strong><?= escape($order['name']) ?></strong><br>
                            <?= nl2br(escape($order['address'])) ?><br>
                            <?= escape($order['city']) ?> · <?= escape($order['pincode']) ?><br>
                            <?= escape($order['phone']) ?>
                        </address>
                    </div>
                <?php endif ?>
            <?php endif ?>
            <a class="he-confirmation-link" href="./">Back to the store <span aria-hidden="true">→</span></a>
        </section>
    </main>
</body>
</html>
