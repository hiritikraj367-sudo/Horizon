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

Sign in at `/admin/`, open **Cashfree settings**, and save your App ID, Secret Key, mode, and storefront HTTPS URL. The dashboard stores these values in `cashfree-settings.json` beside the private order database with file permissions set to `0600`. Keep that directory outside the web root and restrict access to its backups. Saved credentials are never filled into the form. If you change modes, enter both credentials and verify or resolve any pending payments from the previous mode first.

You can also configure the following environment variables. Values saved through the dashboard take precedence. Keep credentials out of Git.

| Variable | Value |
| --- | --- |
| `STORE_URL` | Public HTTPS base URL serving the store |
| `CASHFREE_MODE` | `sandbox` for testing or `production` for live payments |
| `CASHFREE_CLIENT_ID` | Cashfree App ID for the selected mode |
| `CASHFREE_CLIENT_SECRET` | Cashfree secret key for the selected mode |

Allowlist the storefront domain in Cashfree and configure payment success notifications to the storefront's `/webhook.php`. The return page and signed success notifications both verify the order with Cashfree before marking it paid. Start with sandbox credentials and confirm the full flow before enabling production. Store information and policy links open the existing Home Vexa pages.

This setup uses a PHP host with a persistent disk. Running it on ephemeral serverless instances requires a compatible PHP runtime and external persistent order storage.

## Admin dashboard

Open `/admin/` to view delivery details and payment status. Admin access is disabled until you set the `ADMIN_PASSWORD_HASH` environment variable for the PHP process. Generate a hash from a strong unique password on a trusted Bash terminal with PHP installed:

```bash
read -rs -p 'Admin password: ' admin_password
printf '\n'
printf '%s' "$admin_password" | php -r 'echo password_hash(stream_get_contents(STDIN), PASSWORD_DEFAULT), PHP_EOL;'
unset admin_password
```

Set the resulting hash as the value of `ADMIN_PASSWORD_HASH` without expanding its dollar signs. Keep the password and hash out of the repository. Serve the dashboard over HTTPS and configure login rate limiting at your web server. For local testing, HTTP on localhost works. Orders are read from the same SQLite database as checkout.
