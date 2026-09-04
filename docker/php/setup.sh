#!/usr/bin/env sh
set -eu

cd /var/www/html

mkdir -p /var/www/html/vendor /var/www/html/storage /var/www/html/bootstrap/cache /workspace/node_modules /pnpm/store
chown -R minori:minori /var/www/html/vendor /var/www/html/storage /var/www/html/bootstrap/cache /workspace/node_modules /pnpm/store
for dependency_dir in /workspace/apps/*/node_modules /workspace/packages/*/node_modules; do
    if [ -e "$dependency_dir" ]; then
        chown -R minori:minori "$dependency_dir"
    fi
done
if [ -f /var/www/html/.env ]; then
    chown minori:minori /var/www/html/.env
fi

as_minori() {
    su -s /bin/sh minori -c "$1"
}

as_minori 'cd /var/www/html && composer install --no-interaction --prefer-dist'

if [ ! -f .env ]; then
    as_minori 'cd /var/www/html && cp .env.example .env'
fi

if ! grep -Eq '^APP_KEY=base64:.+' .env; then
    as_minori 'cd /var/www/html && php artisan key:generate --force'
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs storage/app/public
chown -R minori:minori storage
php artisan storage:link --force
as_minori 'cd /var/www/html && php artisan migrate --force'
as_minori 'cd /var/www/html && php artisan db:seed --force'

as_minori 'cd /workspace && pnpm install --frozen-lockfile --store-dir /pnpm/store'

echo 'Minori-kun local environment is ready.'
