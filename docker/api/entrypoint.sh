#!/bin/sh
set -e

DB_FILE="/var/www/html/database/database.sqlite"
echo "[entrypoint] Starting SportPlaner API..."

# ── Initialize SQLite ─────────────────────────────
if [ ! -f "$DB_FILE" ]; then
    echo "[entrypoint] Creating SQLite database..."
    touch "$DB_FILE"
    chown www-data:www-data "$DB_FILE"
fi

# ── Run migrations ────────────────────────────────
echo "[entrypoint] Running migrations..."
php artisan migrate --force --no-interaction

# ── Seed on first boot ────────────────────────────
SEED_MARKER="/var/www/html/database/.seeded"
if [ ! -f "$SEED_MARKER" ]; then
    echo "[entrypoint] First boot — running seeders..."
    php artisan db:seed --force --no-interaction
    touch "$SEED_MARKER"
    chown www-data:www-data "$SEED_MARKER"
    echo "[entrypoint] Seed complete."
fi

# ── Cache config & routes ─────────────────────────
echo "[entrypoint] Caching config and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── Fix permissions ───────────────────────────────
chown -R www-data:www-data /var/www/html/storage \
                            /var/www/html/bootstrap/cache \
                            "$DB_FILE"

echo "[entrypoint] Done. Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
