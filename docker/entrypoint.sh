#!/usr/bin/env bash
set -e

APP_ROOT="/app"

: "${APP_ENV:=production}"
: "${APP_DEBUG:=false}"

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:"* ]; then
    php "$APP_ROOT/artisan" key:generate --force
fi

mkdir -p \
    "$APP_ROOT/storage" \
    "$APP_ROOT/storage/logs" \
    "$APP_ROOT/storage/framework" \
    "$APP_ROOT/storage/framework/sessions" \
    "$APP_ROOT/storage/framework/views" \
    "$APP_ROOT/storage/framework/cache" \
    "$APP_ROOT/bootstrap/cache"

chown -R www-data:www-data "$APP_ROOT/storage" "$APP_ROOT/bootstrap/cache"
chmod -R 775 "$APP_ROOT/storage" "$APP_ROOT/bootstrap/cache"

if [ "$APP_ENV" = "production" ]; then
    php /app/artisan config:cache
    php /app/artisan route:cache
    php /app/artisan view:cache
fi

exec php-fpm
