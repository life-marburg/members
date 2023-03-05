FROM composer:2 AS build-php

WORKDIR /var/www
COPY . ./
RUN composer install --optimize-autoloader --ignore-platform-req=php --ignore-platform-req=ext-bcmath

FROM node:18-alpine AS build-frontend

WORKDIR /var/www

COPY package.json ./
COPY pnpm-lock.yaml ./

RUN corepack enable && \
    pnpm install

# To make tailwind purge find templates from vendor
COPY --from=build-php /var/www/vendor /var/www/vendor
COPY . ./

RUN pnpm run prod && rm -rf node_modules

FROM kolaente/laravel:8.2-octane-prod

ENV PHPREDIS_VERSION 5.3.7
RUN mkdir -p /usr/src/php/ext/redis \
    && curl -L https://github.com/phpredis/phpredis/archive/$PHPREDIS_VERSION.tar.gz | tar xvz -C /usr/src/php/ext/redis --strip 1 \
    && echo 'redis' >> /usr/src/php-available-exts \
    && docker-php-ext-install redis

COPY . ./
COPY --from=build-frontend /var/www/public /var/www/public
COPY --from=build-php /var/www/vendor /var/www/vendor
COPY --from=build-php /var/www/bootstrap /var/www/bootstrap
RUN php artisan storage:link
