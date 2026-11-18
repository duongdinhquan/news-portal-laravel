#!/bin/sh
set -eu

if [ ! -f .env ]; then
    echo "Missing .env. Run: cp .env.example .env" >&2
    exit 1
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/public bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

if { [ -z "${APP_KEY:-}" ] && grep -Eq '^APP_KEY=[[:space:]]*$' .env; } \
    || { [ -z "${JWT_SECRET:-}" ] && grep -Eq '^JWT_SECRET=[[:space:]]*$' .env; }; then
    # Clear a stale config cache before generating secrets from the mounted .env file.
    php artisan config:clear >/dev/null 2>&1 || true
fi

if [ -z "${APP_KEY:-}" ] && grep -Eq '^APP_KEY=[[:space:]]*$' .env; then
    echo "[news-portal] APP_KEY is empty; generating Laravel application key."
    php artisan key:generate --force --no-interaction
fi

if [ -z "${JWT_SECRET:-}" ] && grep -Eq '^JWT_SECRET=[[:space:]]*$' .env; then
    echo "[news-portal] JWT_SECRET is empty; generating JWT signing secret."
    php artisan jwt:secret --force --no-interaction
fi

if [ ! -L public/storage ]; then
    php artisan storage:link >/dev/null 2>&1 || true
fi

exec "$@"
