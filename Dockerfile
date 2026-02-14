FROM composer:2 AS build-php

WORKDIR /var/www
COPY . ./
RUN composer install --optimize-autoloader --no-scripts --ignore-platform-req=php --ignore-platform-req=ext-bcmath --ignore-platform-req=ext-gd --ignore-platform-req=ext-intl

FROM node:22-alpine AS build-frontend

WORKDIR /var/www

COPY package.json ./
COPY pnpm-lock.yaml ./
COPY .npmrc ./

RUN corepack enable && \
    pnpm install

# To make tailwind purge find templates from vendor
COPY --from=build-php /var/www/vendor /var/www/vendor
COPY . ./

RUN pnpm run build && rm -rf node_modules

FROM ghcr.io/kolaente/laravel-docker:8.3-octane-frankenphp

RUN apt-get update && apt-get install -y mariadb-client && \
  docker-php-ext-install pdo pdo_mysql && \
  rm -rf /var/lib/apt/lists/*

COPY . ./
COPY --from=build-frontend /var/www/public /app/public
COPY --from=build-php /var/www/vendor /app/vendor
COPY --from=build-php /var/www/bootstrap /app/bootstrap
RUN mkdir -p storage/framework/{cache/data,sessions,views,testing} storage/logs && \
    php artisan storage:link
