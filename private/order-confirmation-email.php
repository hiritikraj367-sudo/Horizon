<?php
declare(strict_types=1);

function renderOrderConfirmationEmail(array $order, string $deliveryWindow, string $url): string
{
    ob_start();
    ?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Home Vexa order confirmation</title></head>
<body style="margin:0;background:#f7f5ef;color:#344536;font:15px/1.6 Arial,Helvetica,sans-serif">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0">
    <tr><td align="center" style="padding:24px 12px">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#fbfaf5;border:1px solid #e3e1d5;border-radius:16px">
            <tr><td style="padding:24px">
                <p style="margin:0 0 16px;font-size:19px;font-weight:bold">Home Vexa</p>
                <h1 style="margin:0;font:30px/1.2 Georgia,serif">Thank you for your order!</h1>
                <p>Your order is confirmed. You can pay when it arrives.</p>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="96" valign="top"><img src="https://homevexa.online/assets/01.webp" alt="<?= escape($order['product']) ?>" width="80" height="80" style="display:block;border-radius:8px"></td>
                        <td valign="middle"><strong><?= escape($order['product']) ?></strong><br>Quantity <?= (int) $order['quantity'] ?> · ₹<?= number_format((int) $order['amount']) ?> total</td>
                    </tr>
                </table>
                <table width="100%" cellspacing="0" cellpadding="0" style="margin-top:20px;background:#f0f2e9;text-align:left">
                    <tr><th scope="row" style="padding:8px">Order reference</th><td style="padding:8px;overflow-wrap:anywhere"><?= escape($order['id']) ?></td></tr>
                    <tr><th scope="row" style="padding:8px">Payment</th><td style="padding:8px">Cash on delivery</td></tr>
                    <tr><th scope="row" style="padding:8px">Estimated delivery</th><td style="padding:8px"><?= escape($deliveryWindow) ?> (3–5 days)</td></tr>
                </table>
                <h2 style="margin:20px 0 6px;font:20px Georgia,serif">Delivery details</h2>
                <p style="margin:0 0 24px;overflow-wrap:anywhere">
                            <strong><?= escape($order['name']) ?></strong><br>
                            <?= nl2br(escape($order['address'])) ?><br>
                            <?= escape($order['city']) ?> · <?= escape($order['pincode']) ?><br>
                            <?= escape($order['phone']) ?>
                </p>
                <a href="<?= escape($url) ?>" style="display:inline-block;padding:10px 16px;background:#3b503d;color:#fffdf7;border-radius:7px;text-decoration:none">View order confirmation</a>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
    <?php
    return ob_get_clean();
}
