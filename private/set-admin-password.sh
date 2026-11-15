#!/bin/bash
set -euo pipefail
umask 077

directory=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
read -r -s -p 'New admin password (12–72 bytes): ' password
printf '\n'
read -r -s -p 'Confirm admin password: ' confirmation
printf '\n'
if [[ "$password" != "$confirmation" ]]; then
    printf 'Passwords did not match.\n' >&2
    exit 1
fi
unset confirmation

temporary=$(mktemp "$directory/.admin-password.XXXXXX")
trap 'rm -f -- "$temporary"' EXIT
printf '%s' "$password" | php -r '
    $password = stream_get_contents(STDIN);
    if (strlen($password) < 12 || strlen($password) > 72) {
        fwrite(STDERR, "Use a password between 12 and 72 bytes.\n");
        exit(1);
    }
    echo password_hash($password, PASSWORD_DEFAULT), PHP_EOL;
' > "$temporary"
unset password
mv -fT -- "$temporary" "$directory/admin-password.hash"
printf 'Admin password configured.\n'
