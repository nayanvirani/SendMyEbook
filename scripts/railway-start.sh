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

    # No --max-time: Railway's restart policy only restarts a service on a
    # *non-zero* exit. --max-time makes queue:work exit 0 (clean stop) once
    # it hits the limit, which Railway does NOT treat as a failure — the
    # worker silently stopped processing every job with no restart and no
    # error anywhere. Running indefinitely, restarted only on an actual
    # crash, is what we actually want here.
    exec php artisan queue:work --tries=3
fi

php artisan migrate --force --no-interaction
php artisan config:cache
php artisan route:cache
php artisan storage:link || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
