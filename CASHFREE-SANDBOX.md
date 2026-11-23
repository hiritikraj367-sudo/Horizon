# Cashfree sandbox test for Home Vexa

Cashfree checkout can now run against sandbox or production without code edits. The environment is selected by the private cPanel file:

`/home/homevexa/homevexa-private/data/cashfree.json`

For sandbox testing, **back up the current production config outside public_html** and replace that file with:

```json
{
  "environment": "sandbox",
  "app_id": "YOUR_CASHFREE_TEST_APP_ID",
  "secret_key": "YOUR_CASHFREE_TEST_SECRET_KEY"
}
```

Get the App ID and Secret Key from the **Cashfree merchant dashboard in Test environment**, not Live. Never commit credentials to GitHub or store them under public_html. The example file is not deployed with code.

Pull the latest branch and execute `deploy-cpanel.sh` as usual. The deploy script does **not** overwrite `cashfree.json`.

Open checkout and select **Cashfree TEST payment**. The server uses `https://sandbox.cashfree.com/pg/` and the JS SDK uses `mode: 'sandbox'`. Sandbox records are stored as `cashfree_test` / `test_pending` / `test_paid` and must **never be fulfilled as customer orders**. No real money is charged in sandbox.

To check connectivity from cPanel Terminal without payment:

```sh
php /home/homevexa/homevexa-private/test-cashfree.php
```

To return to live, restore your production credentials and change `environment` to `production`; ensure the merchant account is activated and complete real-payment validation before accepting customers. Sandbox payments are not compatible with production verification. Avoid switching while a sandbox checkout is in progress.

**Note:** This integration relies on the browser return URL to reconcile payments; a verified Cashfree webhook is not yet implemented and must be added before relying on unattended production payments.
