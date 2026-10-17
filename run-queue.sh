#!/usr/bin/env bash
set -e

# Pindah ke direktori project Laravel
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

# Deteksi binary PHP (CyberPanel / LSWS / standard Linux)
if [ -x "/usr/local/lsws/lsphp84/bin/php" ]; then
    PHP_BIN="/usr/local/lsws/lsphp84/bin/php"
elif command -v php84 &>/dev/null; then
    PHP_BIN="$(command -v php84)"
elif [ -x "/usr/bin/php84" ]; then
    PHP_BIN="/usr/bin/php84"
elif command -v php &>/dev/null; then
    PHP_BIN="$(command -v php)"
else
    PHP_BIN="php"
fi

echo "🚀 Menjalankan Didispen Queue Worker dengan: $PHP_BIN"
exec "$PHP_BIN" artisan queue:work --tries=3 --backoff=10
