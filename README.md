# Home Vexa

A PHP storefront for the Home Vexa five-piece silicone scrubber set. The Buy Now dialog collects name, mobile number, delivery address, city and PIN. It supports ₹239 cash on delivery.

## Run locally

Requires PHP 8.1+ with PDO MySQL (`pdo_mysql`).

```sh
php -S 127.0.0.1:8000 -t public
```

Open http://127.0.0.1:8000. On a PHP host, point the web server document root at `public/`. Keep `private/` outside the document root. Give the PHP process write access to a private, persistent directory.

## Orders

Create a MySQL or MariaDB database and database user. Copy the example below into the app's private directory as `database.json`. Use your full cPanel database and user names, including their account prefixes. Keep credentials out of Git and the web root.

```json
{
  "host": "localhost",
  "database": "dynamics_homevexa",
  "username": "dynamics_homevexa",
  "password": "USE_YOUR_DATABASE_USER_PASSWORD"
}
```

The app creates the `orders` and `admin_credentials` tables on first use. Give the database user CREATE, SELECT, INSERT and UPDATE privileges on this database. View orders in **cPanel → phpMyAdmin → your database → orders → Browse**. Existing SQLite files are left in place and require a separate data migration if they contain orders to retain.

New orders use `cod_confirmed`. Historical order records and their payment statuses remain visible in the admin dashboard.

This setup uses a PHP host with a persistent disk. Running it on ephemeral serverless instances requires a compatible PHP runtime and external persistent order storage.

## Admin dashboard

Open `/admin/` to view delivery details and payment status. Admin access requires a configured password hash. Use the private setup script on cPanel or set the `ADMIN_PASSWORD_HASH` environment variable for the PHP process. The environment variable takes precedence, followed by the MySQL credential and then the private password-hash file. Generate a hash from a strong unique password on a trusted Bash terminal with PHP installed:

```bash
read -rs -p 'Admin password: ' admin_password
printf '\n'
printf '%s' "$admin_password" | php -r 'echo password_hash(stream_get_contents(STDIN), PASSWORD_DEFAULT), PHP_EOL;'
unset admin_password
```

Set the resulting hash as the value of `ADMIN_PASSWORD_HASH` without expanding its dollar signs. Keep the password and hash out of the repository. Serve the dashboard over HTTPS and configure login rate limiting at your web server. For local testing, HTTP on localhost works. Orders are read from the same MySQL database as checkout.

## Deploy the test subdomain with cPanel

For `test.dynamicsorter.in`, keep the existing document root `/home/dynamics/public_html/test.dynamicsorter.in`. Clone this repository in cPanel Git Version Control under `/home/dynamics/repositories/homevexa`. In **Manage → Pull or Deploy**, select **Update from Remote** then **Deploy HEAD Commit**.

The deployment configuration copies public files to the test document root and PHP internals to `/home/dynamics/homevexa-test-private`. Use **File Manager** to create `/home/dynamics/homevexa-test-private/database.json` with the settings above and set its permissions to `0600`. The deployment preserves this file and the private admin hash file. After deployment, choose the admin password in **cPanel → Terminal**:

```bash
bash /home/dynamics/homevexa-test-private/set-admin-password.sh
```

The script prompts twice and stores only a hash in the private directory. Redeployments preserve the hash. If a hosting environment already sets `ADMIN_PASSWORD_HASH`, remove or update that variable to use the new password. If the subdomain displays a default hosting page, check whether its document root still contains a placeholder `index.html`.


To configure the credential in MySQL instead, generate a password hash as shown above and run this in cPanel Terminal after deployment:

```bash
php /home/dynamics/homevexa-test-private/set-admin-hash.php 'PASTE_PASSWORD_HASH_HERE'
```

Run it again with a new hash to rotate the password. Existing admin sessions are invalidated when the active hash changes.
