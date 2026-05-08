#!/usr/bin/env bash
set -e

# ─── Validate critical environment ───────────────────────────────────────────
if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set. Run: fly secrets set APP_KEY=base64:..." >&2
    exit 1
fi

# ─── Ensure the volume directory is writable by PHP-FPM ─────────────────────
chown www-data:www-data /data

# ─── Create SQLite database file on the persistent volume ────────────────────
touch /data/database.sqlite
chown www-data:www-data /data/database.sqlite

# ─── Ensure storage directories exist and are writable ───────────────────────
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage

# ─── Symlink storage/app to the volume for uploaded files persistence ─────────
if [ ! -L /var/www/html/storage/app ]; then
    mkdir -p /data/storage-app
    rm -rf /var/www/html/storage/app
    ln -s /data/storage-app /var/www/html/storage/app
    chown -h www-data:www-data /var/www/html/storage/app
fi
chown -R www-data:www-data /data/storage-app 2>/dev/null || true

# ─── Run migrations ───────────────────────────────────────────────────────────
echo "Running migrations..."
/usr/bin/php /var/www/html/artisan migrate --force --no-ansi 2>&1 || {
    echo "ERROR: Migrations failed" >&2
    exit 1
}

# ─── Seed database (uses firstOrCreate — safe to re-run) ─────────────────────
echo "Seeding database..."
/usr/bin/php /var/www/html/artisan db:seed --force --no-ansi 2>&1 || {
    echo "WARNING: Seeding failed (may already be seeded)" >&2
}

# ─── Cache config, routes, and views ─────────────────────────────────────────
echo "Caching config, routes, and views..."
/usr/bin/php /var/www/html/artisan config:cache --no-ansi
/usr/bin/php /var/www/html/artisan route:cache --no-ansi
/usr/bin/php /var/www/html/artisan view:cache --no-ansi

echo "Startup complete."
