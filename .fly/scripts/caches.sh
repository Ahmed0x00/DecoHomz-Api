#!/usr/bin/env bash
# NOTE: Do NOT use 'set -e' here — the entrypoint.sh bails if this script
# exits non-zero, which prevents supervisord/nginx/php-fpm from starting.

# ─── Validate critical environment ───────────────────────────────────────────
if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY is not set! App will likely fail to start." >&2
    echo "Fix with: fly secrets set APP_KEY=\"base64:...\" --app decohomz-api" >&2
fi

# ─── Ensure the volume directory is writable by PHP-FPM ─────────────────────
chown www-data:www-data /data || echo "WARNING: Could not chown /data" >&2

# ─── Create SQLite database file on the persistent volume ────────────────────
touch /data/database.sqlite
chown www-data:www-data /data/database.sqlite

# ─── Ensure all storage directories exist and are writable ───────────────────
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage

# ─── Symlink storage/app to the volume for persistent uploaded files ──────────
if [ ! -L /var/www/html/storage/app ]; then
    mkdir -p /data/storage-app
    rm -rf /var/www/html/storage/app
    ln -s /data/storage-app /var/www/html/storage/app
    chown -h www-data:www-data /var/www/html/storage/app
fi
chown -R www-data:www-data /data/storage-app 2>/dev/null || true

# ─── Run migrations ───────────────────────────────────────────────────────────
echo "Running migrations..."
if /usr/bin/php /var/www/html/artisan migrate --force --no-ansi 2>&1; then
    echo "Migrations completed successfully."
else
    echo "ERROR: Migrations failed. Check logs above." >&2
fi

# ─── Seed database (firstOrCreate — safe to re-run) ──────────────────────────
echo "Seeding database..."
if /usr/bin/php /var/www/html/artisan db:seed --force --no-ansi 2>&1; then
    echo "Seeding completed."
else
    echo "WARNING: Seeding failed or already seeded." >&2
fi

# ─── Cache config, routes, and views ─────────────────────────────────────────
echo "Caching application..."
/usr/bin/php /var/www/html/artisan config:cache --no-ansi 2>&1 || echo "WARNING: config:cache failed" >&2
/usr/bin/php /var/www/html/artisan route:cache --no-ansi 2>&1  || echo "WARNING: route:cache failed" >&2
/usr/bin/php /var/www/html/artisan view:cache --no-ansi 2>&1   || echo "WARNING: view:cache failed" >&2

echo "Startup script complete. Handing off to supervisord..."
exit 0

