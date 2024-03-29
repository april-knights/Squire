#!/usr/bin/env bash

chown :www-data bootstrap/cache \
                storage/app/public \
                storage/framework/{cache,sessions,testing,views} \
                storage/framework/cache/data \
                storage/logs

# Wait for the database to start before running migrations or running artisan commands at all
wait-for-it database:3306 -t 0 || exit 1

# Copy the default environment for first run and make it writable by anyone so that it can be edited
[ ! -f .env ] && cp .env.default .env && chmod ugo+rw .env && php artisan key:generate
# Or just set the key if not set yet
grep APP_KEY=CHANGEME .env > /dev/null && php artisan key:generate

# Install packages
/usr/bin/composer install --no-interaction --prefer-dist

gosu www-data php artisan package:discover --ansi

gosu www-data php artisan migrate

exec docker-php-entrypoint "$@"
