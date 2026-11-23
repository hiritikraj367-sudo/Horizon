#!/bin/bash
set -euo pipefail

repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
account_home=${1:-/home/homevexa}
document_root="$account_home/public_html"
private_root="$account_home/homevexa-private"

if [[ ! -d "$document_root" || -L "$document_root" || -L "$private_root" ]]; then
    printf 'Expected a cPanel public_html document root and a private directory outside public_html.\n' >&2
    exit 1
fi
if [[ "$(realpath "$document_root")" != "$(realpath "$account_home")/public_html" ]]; then
    printf 'The cPanel document root resolves to an unexpected directory.\n' >&2
    exit 1
fi

install -d -m 0700 "$private_root"
install -m 0600 "$repo/private/store.php" "$repo/private/order-confirmation-email.php" "$repo/private/set-admin-hash.php" "$private_root/"

install -m 0700 "$repo/private/set-admin-password.sh" "$private_root/"

stage=$(mktemp -d)
trap 'rm -rf -- "$stage"' EXIT
cp -R "$repo/public/." "$stage/"
cat > "$stage/bootstrap.php" <<'PHP'
<?php
declare(strict_types=1);

require dirname(__DIR__) . '/homevexa-private/store.php';
PHP
cp -R "$stage/." "$document_root/"

# Remove only legacy dynamic landing-page artifacts from public_html.
# Do not remove unrelated content or follow directory symlinks.
for legacy_file in landing.php landing-product.php landing-order.php assets/landing-store.js; do
    target="$document_root/$legacy_file"
    if [[ -f "$target" || -L "$target" ]]; then
        rm -f -- "$target"
    fi
done
for legacy_dir in landing-page admin/landing-pages; do
    target="$document_root/$legacy_dir"
    if [[ -L "$target" ]]; then
        rm -f -- "$target"
    elif [[ -d "$target" ]]; then
        rm -rf -- "$target"
    fi
done
rm -f -- "$document_root/payment.php" "$document_root/webhook.php" "$private_root/cashfree-settings.php"
printf 'Deployed Home Vexa to %s\n' "$document_root"
