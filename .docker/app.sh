#!/usr/bin/env bash

chown :www-data bootstrap/cache \
                storage/app/public \
                storage/framework/{cache,sessions,testing,views} \
                storage/framework/cache/data \
                storage/logs

# Copy the default environment for first run and make it writable by anyone so that it can be edited
[ ! -f .env ] && cp .env.default .env && chmod ugo+rw .env

# Generate app key if none is specified
[[ -z $APP_KEY ]] && php artisan key:generate

# Install packages
/usr/bin/composer install --no-interaction --prefer-dist

gosu www-data php artisan package:discover --ansi

# Wait for the database to start before running migrations
wait-for-it squire-database:3306 -t 0 || exit 1

gosu www-data php artisan migrate

exec docker-php-entrypoint "$@"
