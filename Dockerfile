# syntax=docker/dockerfile:1

FROM php:8.4-fpm-bookworm AS app

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:22-bookworm /usr/local /usr/local

RUN npm install -g pnpm@latest \
    && install-php-extensions pcntl pdo_pgsql redis intl zip bcmath opcache sockets \
    && apt-get update \
    && apt-get install -y --no-install-recommends unzip git \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction

COPY package.json pnpm-lock.yaml pnpm-workspace.yaml* .npmrc* ./
RUN pnpm install --frozen-lockfile

COPY . .

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_KEY=base64:ZHVtbXktYnVpbGQta2V5LW5vdC1mb3ItcHJ1bj09 \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/tmp/build.sqlite

RUN touch /tmp/build.sqlite \
    && composer dump-autoload --optimize --no-dev --no-interaction \
    && php artisan package:discover --ansi

ARG VITE_APP_NAME=DevPulse
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME
ENV VITE_APP_NAME=$VITE_APP_NAME \
    VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY \
    VITE_REVERB_HOST=$VITE_REVERB_HOST \
    VITE_REVERB_PORT=$VITE_REVERB_PORT \
    VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME

RUN pnpm run build \
    && rm -rf node_modules /root/.local /root/.npm /tmp/corepack-cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && rm -f /tmp/build.sqlite

ENV APP_KEY= \
    DB_DATABASE= \
    DB_CONNECTION=pgsql

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && sed -i 's/listen = 127.0.0.1:9000/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

FROM nginx:1.27-alpine AS nginx

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public