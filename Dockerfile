FROM composer:2 AS build-php

WORKDIR /var/www
COPY . ./
RUN composer install --optimize-autoloader --ignore-platform-req=php --ignore-platform-req=ext-bcmath --ignore-platform-req=ext-gd

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

RUN pnpm run prod && rm -rf node_modules

FROM kolaente/laravel:8.3-octane-prod

COPY . ./
COPY --from=build-frontend /var/www/public /var/www/public
COPY --from=build-php /var/www/vendor /var/www/vendor
COPY --from=build-php /var/www/bootstrap /var/www/bootstrap
RUN php artisan storage:link
