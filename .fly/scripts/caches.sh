#!/usr/bin/env bash

# Ensure the volume directory is writable by PHP-FPM
chown www-data:www-data /data

# Create SQLite database file on the persistent volume if it doesn't exist
touch /data/database.sqlite
chown www-data:www-data /data/database.sqlite

# Ensure storage directories exist and are writable
chown -R www-data:www-data /var/www/html/storage

# Symlink storage/app to the volume for uploaded files persistence
if [ ! -L /var/www/html/storage/app ]; then
    mkdir -p /data/storage-app
    rm -rf /var/www/html/storage/app
    ln -s /data/storage-app /var/www/html/storage/app
    chown -h www-data:www-data /var/www/html/storage/app
fi

# Run migrations
/usr/bin/php /var/www/html/artisan migrate --force --no-ansi -q

# Seed database (uses firstOrCreate — safe to re-run)
/usr/bin/php /var/www/html/artisan db:seed --force --no-ansi -q

# Cache config, routes, and views
/usr/bin/php /var/www/html/artisan config:cache --no-ansi -q
/usr/bin/php /var/www/html/artisan route:cache --no-ansi -q
/usr/bin/php /var/www/html/artisan view:cache --no-ansi -q
