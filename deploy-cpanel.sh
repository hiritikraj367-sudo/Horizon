#!/bin/bash
set -euo pipefail

repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
account_home=${1:-/home/dynamics}
document_root="$account_home/public_html/test.dynamicsorter.in"
private_root="$account_home/homevexa-test-private"

if [[ ! -d "$document_root" || -L "$document_root" || -L "$private_root" ]]; then
    printf 'Expected a dedicated test subdomain document root and a private directory outside public_html.\n' >&2
    exit 1
fi
if [[ "$(realpath "$document_root")" != "$(realpath "$account_home")/public_html/test.dynamicsorter.in" ]]; then
    printf 'The test document root resolves to an unexpected directory.\n' >&2
    exit 1
fi

install -d -m 0700 "$private_root"
install -m 0600 "$repo/private/store.php" "$repo/private/cashfree-settings.php" "$private_root/"

install -m 0700 "$repo/private/set-admin-password.sh" "$private_root/"

stage=$(mktemp -d)
trap 'rm -rf -- "$stage"' EXIT
cp -R "$repo/public/." "$stage/"
cat > "$stage/bootstrap.php" <<'PHP'
<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/homevexa-test-private/store.php';
PHP
cp -R "$stage/." "$document_root/"
printf 'Deployed Home Vexa to %s\n' "$document_root"
