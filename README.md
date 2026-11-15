# Home Vexa

A PHP storefront for the Home Vexa five-piece silicone scrubber set. The Buy Now dialog collects name, mobile number, delivery address, city and PIN. It supports ₹239 cash on delivery and an optional ₹200 Cashfree checkout.

## Run locally

Requires PHP 8.1+ with PDO SQLite. The cURL extension is also required for Cashfree.

```sh
php -S 127.0.0.1:8000 -t public
```

Open http://127.0.0.1:8000. On a PHP host, point the web server document root at `public/`. Keep `private/` outside the document root. Give the PHP process write access to a private, persistent directory. COD works without payment credentials.

## Orders

SQLite is initialized on the first order. The default database is `private/data/orders.sqlite`. Set `ORDER_DB_PATH` to an absolute path outside the document root to use another persistent directory. The database contains customer delivery details. Restrict access and include it in private backups. The database is excluded from Git.

To inspect recent orders from the server:

```sh
sqlite3 -header -column private/data/orders.sqlite \
  'SELECT id, name, phone, address, city, pincode, payment, amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT 20;'
```

`cod_confirmed` means the customer chose to pay at delivery. `pending_payment` means the online order is awaiting verification. Only `paid` represents a Cashfree-confirmed payment.

## Enable Cashfree

Set these environment variables on the host. Keep credentials out of Git.

| Variable | Value |
| --- | --- |
| `STORE_URL` | Public HTTPS base URL serving the store |
| `CASHFREE_MODE` | `sandbox` for testing or `production` for live payments |
| `CASHFREE_CLIENT_ID` | Cashfree App ID for the selected mode |
| `CASHFREE_CLIENT_SECRET` | Cashfree secret key for the selected mode |

Allowlist the storefront domain in Cashfree and configure payment success notifications to the storefront's `/webhook.php`. The return page and signed success notifications both verify the order with Cashfree before marking it paid. Start with sandbox credentials and confirm the full flow before enabling production. Store information and policy links open the existing Home Vexa pages.

This setup uses a PHP host with a persistent disk. Running it on ephemeral serverless instances requires a compatible PHP runtime and external persistent order storage.
