#!/usr/bin/env sh
set -eu

cd /var/www/html

bool_true() {
    value="$(printf '%s' "${1:-}" | tr '[:upper:]' '[:lower:]')"

    case "$value" in
        1|true|yes|on)
            return 0
            ;;
        *)
            return 1
            ;;
    esac
}

clear_runtime_caches() {
    rm -f bootstrap/cache/config.php
    rm -f bootstrap/cache/events.php
    rm -f bootstrap/cache/routes*.php
}

container_optimize="${CONTAINER_OPTIMIZE:-auto}"

if [ "$container_optimize" = "auto" ]; then
    if [ "${APP_ENV:-local}" = "production" ]; then
        container_optimize="true"
    else
        container_optimize="false"
    fi
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache

if bool_true "${CONTAINER_RUN_STORAGE_LINK:-true}"; then
    php artisan storage:link || true
fi

if bool_true "${CONTAINER_RUN_MIGRATIONS:-false}"; then
    php artisan migrate --force
fi

if bool_true "${CONTAINER_RUN_SEEDERS:-false}"; then
    php artisan db:seed --force
fi

clear_runtime_caches

if bool_true "$container_optimize"; then
    php artisan optimize
fi

runtime="${CONTAINER_RUNTIME:-serve}"

case "$runtime" in
    serve)
        exec php artisan serve --host="${CONTAINER_HOST:-0.0.0.0}" --port="${APP_PORT:-8000}"
        ;;
    fpm)
        exec php-fpm -F
        ;;
    *)
        echo "Unsupported CONTAINER_RUNTIME: $runtime" >&2
        exit 1
        ;;
esac
