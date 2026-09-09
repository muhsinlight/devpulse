# syntax=docker/dockerfile:1

FROM php:8.4-fpm-bookworm AS php-runtime

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
        pcntl pdo_pgsql pdo_sqlite redis intl zip bcmath opcache sockets \
    && apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates \
    && rm -rf /var/lib/apt/lists/* \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/listen = 127.0.0.1:9000/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

FROM php-runtime AS php-build

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

FROM php-build AS php-vendor

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction

FROM php-build AS php-code

COPY --from=php-vendor /var/www/html/vendor ./vendor
COPY . .

# ENV beats Coolify's injected DB_* ARGs for every later RUN in this stage.
# This stage is not the runtime image, so dummy values never ship as app ENV.
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_KEY=base64:ZHVtbXktYnVpbGQta2V5LW5vdC1mb3ItcHJ1bj09 \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/tmp/build.sqlite \
    CACHE_STORE=array \
    SESSION_DRIVER=array \
    QUEUE_CONNECTION=sync \
    BROADCAST_CONNECTION=log

RUN mkdir -p \
        storage/app/public \
        storage/app/private \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && composer dump-autoload --optimize --no-dev --no-scripts --no-interaction \
    && rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
    && touch /tmp/build.sqlite \
    && php artisan package:discover --ansi \
    && php artisan wayfinder:generate --with-form --no-interaction \
    && rm -f /tmp/build.sqlite

FROM node:22-bookworm AS assets

WORKDIR /app

RUN npm install -g pnpm@12.0.0

COPY package.json pnpm-lock.yaml pnpm-workspace.yaml .npmrc ./
RUN pnpm install --frozen-lockfile --config.production=false

COPY . .
COPY --from=php-code /var/www/html/vendor ./vendor
COPY --from=php-code /var/www/html/resources/js ./resources/js

ARG VITE_APP_NAME=DevPulse
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME
ENV VITE_APP_NAME=$VITE_APP_NAME \
    VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY \
    VITE_REVERB_HOST=$VITE_REVERB_HOST \
    VITE_REVERB_PORT=$VITE_REVERB_PORT \
    VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME \
    WAYFINDER_COMMAND=true \
    CI=true

RUN pnpm exec vite build \
    && test -f public/build/manifest.json \
    && rm -rf node_modules

FROM php-runtime AS app

COPY --from=php-code /var/www/html /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && test -f public/build/manifest.json

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

FROM nginx:1.27-alpine AS nginx

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public
