#!/usr/bin/env bash
set -e

APP_ROOT="/app"

: "${APP_ENV:=production}"
: "${APP_DEBUG:=false}"

rm -rf bootstrap/cache/*


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
find "$APP_ROOT/storage" -type d -exec chmod 775 {} \;
find "$APP_ROOT/storage" -type f -exec chmod 664 {} \;


php $APP_ROOT/artisan optimize

if [ "$APP_ENV" = "production" ]; then
    php $APP_ROOT/artisan config:cache
    php $APP_ROOT/artisan route:cache
    php $APP_ROOT/artisan view:cache
fi

exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
