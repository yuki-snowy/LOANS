#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
  cp .env.example .env
fi

if [ ! -d vendor ]; then
  echo "Installing Composer dependencies with PHP $(php -r 'echo PHP_VERSION;') ..."
  composer install --no-interaction --prefer-dist
fi

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  php artisan key:generate --force --ansi || true
fi

if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
  echo "Waiting for database / running migrations..."
  i=0
  until php artisan migrate --force --no-interaction; do
    i=$((i + 1))
    if [ "$i" -ge 30 ]; then
      echo "Migration still failing after retries. Continuing startup; run: docker compose exec app php artisan migrate"
      break
    fi
    echo "DB not ready yet (attempt ${i}/30)..."
    sleep 2
  done
fi

if [ ! -L public/storage ] && [ ! -e public/storage ]; then
  php artisan storage:link || true
fi

exec "$@"
