#!/usr/bin/env bash
set -euo pipefail

# ---------------------------------------------------------------------------
# Container entrypoint. Runs once at boot, before supervisor starts the
# long-running processes.
# ---------------------------------------------------------------------------

APP_DIR=/var/www/html
cd "$APP_DIR"

# 1. Bind nginx to the platform-provided port ($PORT). Render/Fly/Railway all
#    inject it; fall back to 8080 for local `docker run`.
: "${PORT:=8080}"
sed -i "s/__PORT__/${PORT}/g" /etc/nginx/nginx.conf
mkdir -p /run/nginx

# 2. Ensure runtime dirs are writable (bind mounts can reset ownership).
chown -R www-data:www-data storage bootstrap/cache || true

# 3. Generate an APP_KEY if the platform didn't supply one. Prefer setting
#    APP_KEY as an env var in production so it stays stable across deploys.
if [ -z "${APP_KEY:-}" ]; then
    echo "[entrypoint] APP_KEY not set — generating an ephemeral one."
    php artisan key:generate --force --no-interaction || true
fi

# 4. Run database migrations. --force is required outside 'local' env.
#    --graceful means a failure here won't crash the boot (e.g. DB warming up).
echo "[entrypoint] Running migrations..."
php artisan migrate --force --graceful --no-interaction || \
    echo "[entrypoint] WARNING: migrations did not complete cleanly."

# 5. Link the public storage dir (idempotent).
php artisan storage:link --no-interaction || true

# 6. Cache config/routes/views/events for production performance.
echo "[entrypoint] Caching framework state..."
php artisan config:cache --no-interaction || \
    echo "[entrypoint] Skipping config cache."
# route:cache fails if any route uses a closure (routes/web.php has a few).
# Keep it non-fatal so a closure route never blocks the whole boot.
php artisan route:cache  --no-interaction || \
    echo "[entrypoint] Skipping route cache (closure routes present)."
php artisan view:cache   --no-interaction || \
    echo "[entrypoint] Skipping view cache."
php artisan event:cache  --no-interaction || true

echo "[entrypoint] Boot complete. Handing off to: $*"
exec "$@"
