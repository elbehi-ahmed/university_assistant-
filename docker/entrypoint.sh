#!/bin/sh
# Container start-up: wait for the database, run migrations, create the first
# admin and (optionally) load demo data, then hand over to Apache.
set -e

# An empty APP_SECRET passed by Compose would hide the value from .env.dev.
if [ -z "$APP_SECRET" ]; then
    unset APP_SECRET
    if [ "$APP_ENV" = "prod" ]; then
        APP_SECRET=$(php -r 'echo bin2hex(random_bytes(16));')
        export APP_SECRET
        echo "[entrypoint] WARNING: APP_SECRET is not set; generated a temporary one." \
             "Sessions will be lost on restart. Set APP_SECRET in production."
    fi
fi

if [ "$1" = "apache2-foreground" ]; then
    echo "[entrypoint] Waiting for the database..."
    attempts=0
    until php bin/console dbal:run-sql -q "SELECT 1" >/dev/null 2>&1; do
        attempts=$((attempts + 1))
        if [ "$attempts" -ge 60 ]; then
            echo "[entrypoint] Database is still unreachable after 120s, giving up."
            php bin/console dbal:run-sql "SELECT 1" || true
            exit 1
        fi
        sleep 2
    done
    echo "[entrypoint] Database is up."

    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --all-or-nothing

    if [ -n "$ADMIN_EMAIL" ] && [ -n "$ADMIN_PASSWORD" ]; then
        php bin/console app:create-admin "$ADMIN_EMAIL" --password="$ADMIN_PASSWORD" --if-not-exists
    fi

    if [ "$SEED_DEMO_DATA" = "1" ]; then
        php bin/console app:seed-demo --if-empty
    fi

    # Commands above ran as root; give the cache back to Apache's user.
    chown -R www-data:www-data var
fi

exec docker-php-entrypoint "$@"
