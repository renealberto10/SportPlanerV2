#!/bin/sh
set -e

DB_FILE="/var/www/data/database.sqlite"
SEED_MARKER="/var/www/data/.seeded"
echo "[entrypoint] Starting SportPlaner API..."

# ── Ensure data directory exists ──────────────────────────
mkdir -p /var/www/data
chown www-data:www-data /var/www/data

# ── Migrate DB from old location (database/) if needed ────
OLD_DB="/var/www/html/database/database.sqlite"
if [ -f "$OLD_DB" ] && [ ! -f "$DB_FILE" ]; then
    echo "[entrypoint] Migrating database from old location..."
    cp "$OLD_DB" "$DB_FILE"
    chown www-data:www-data "$DB_FILE"
fi

# ── Migrate seeded marker from old location if needed ─────
OLD_SEED="/var/www/html/database/.seeded"
if [ -f "$OLD_SEED" ] && [ ! -f "$SEED_MARKER" ]; then
    cp "$OLD_SEED" "$SEED_MARKER"
fi

# ── Initialize SQLite ─────────────────────────────────────
if [ ! -f "$DB_FILE" ]; then
    echo "[entrypoint] Creating SQLite database..."
    touch "$DB_FILE"
    chown www-data:www-data "$DB_FILE"
fi

# ── Run migrations ────────────────────────────────────────
echo "[entrypoint] Running migrations..."
php artisan migrate --force --no-interaction

# ── Seed on first boot ────────────────────────────────────
if [ ! -f "$SEED_MARKER" ]; then
    echo "[entrypoint] First boot — running seeders..."
    php artisan db:seed --force --no-interaction
    touch "$SEED_MARKER"
    chown www-data:www-data "$SEED_MARKER"
    echo "[entrypoint] Seed complete."
fi

# ── Ensure required storage directories exist ─────
# (Docker volume overwrites image-layer dirs; they must be recreated at runtime)
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views
mkdir -p storage/logs bootstrap/cache storage/app/public

# ── Storage symlink (public/storage → storage/app/public) ─
php artisan storage:link --force 2>/dev/null || true

# ── Cache config & routes ─────────────────────────
echo "[entrypoint] Caching config and routes..."
php artisan config:cache
php artisan route:cache

# ── Fix permissions ───────────────────────────────
chown -R www-data:www-data /var/www/html/storage \
                            /var/www/html/bootstrap/cache \
                            "$DB_FILE"

echo "[entrypoint] Done. Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
