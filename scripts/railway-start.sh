#!/usr/bin/env bash
# Shared Railway start command for both the web service and the queue
# worker service (see Procfile). Which one a given Railway service runs is
# controlled entirely by its PROCESS_TYPE variable — same image, same
# script, no separate build needed for the worker.
set -e

if [ "${PROCESS_TYPE:-web}" = "worker" ]; then
    # Build-time config:cache below bakes in whatever variables were set on
    # THIS service at build time; clear it so the worker always reads the
    # variables actually present at container start.
    php artisan config:clear
    exec php artisan queue:work --tries=3 --max-time=3600
fi

php artisan migrate --force --no-interaction
php artisan config:cache
php artisan route:cache
php artisan storage:link || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
