FROM node:22.20.0-bookworm-slim AS node_runtime

FROM php:8.4.13-fpm-bookworm AS php_base

ARG APP_UID=1000
ARG APP_GID=1000

RUN sed -i 's|http://deb.debian.org|https://deb.debian.org|g' /etc/apt/sources.list.d/debian.sources \
    && apt-get update \
    && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" bcmath intl pcntl pdo_mysql opcache zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.8.12 /usr/bin/composer /usr/local/bin/composer
COPY --from=node_runtime /usr/local/ /usr/local/

RUN corepack prepare pnpm@10.17.1 --activate \
    && groupadd --gid ${APP_GID} minori \
    && useradd --uid ${APP_UID} --gid ${APP_GID} --create-home --shell /bin/bash minori \
    && sed -i 's/^user = .*/user = minori/; s/^group = .*/group = minori/' /usr/local/etc/php-fpm.d/www.conf

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-minori.ini
WORKDIR /var/www/html

FROM php_base AS development
USER minori
CMD ["php-fpm", "-F"]

FROM php_base AS vendor_production
COPY apps/api/composer.json apps/api/composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-req=ext-pcntl

FROM php_base AS production
COPY --chown=minori:minori apps/api/ ./
COPY --from=vendor_production --chown=minori:minori /var/www/html/vendor ./vendor
RUN php artisan package:discover --ansi \
    && php artisan config:clear \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs \
    && chown -R minori:minori storage bootstrap/cache
USER minori
CMD ["php-fpm", "-F"]
