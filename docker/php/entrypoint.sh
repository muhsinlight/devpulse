#!/bin/bash
set -euo pipefail

cd /var/www/html

mkdir -p \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/framework/testing \
    storage/logs \
    storage/app/public \
    storage/app/private \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

wait_for_tcp() {
    local host="$1"
    local port="$2"
    local name="$3"
    php -r '
        $host = $argv[1];
        $port = (int) $argv[2];
        $name = $argv[3];
        for ($i = 0; $i < 60; $i++) {
            $socket = @fsockopen($host, $port, $errno, $errstr, 1);
            if ($socket) {
                fclose($socket);
                exit(0);
            }
            sleep(1);
        }
        fwrite(STDERR, $name." is not reachable at ".$host.":".$port.PHP_EOL);
        exit(1);
    ' -- "$host" "$port" "$name"
}

as_www() {
    runuser -u www-data -- "$@"
}

role="${CONTAINER_ROLE:-app}"

if [[ "$role" != "app" && "$role" != "worker" && "$role" != "scheduler" && "$role" != "reverb" ]]; then
    exec "$@"
fi

wait_for_tcp "${DB_HOST:-postgres}" "${DB_PORT:-5432}" "postgres"
wait_for_tcp "${REDIS_HOST:-redis}" "${REDIS_PORT:-6379}" "redis"

as_www php artisan storage:link --force >/dev/null 2>&1 || true
as_www php artisan optimize

case "$role" in
    app)
        as_www php artisan migrate --force
        exec php-fpm
        ;;
    worker)
        exec runuser -u www-data -- php artisan queue:work redis \
            --sleep=1 \
            --tries=3 \
            --timeout=120 \
            --max-time=3600
        ;;
    scheduler)
        exec runuser -u www-data -- php artisan schedule:work
        ;;
    reverb)
        exec runuser -u www-data -- php artisan reverb:start \
            --host=0.0.0.0 \
            --port=8080 \
            --no-interaction
        ;;
esac
