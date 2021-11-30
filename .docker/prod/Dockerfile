FROM composer:2 AS build-php

WORKDIR /var/www
COPY . ./
RUN composer install --optimize-autoloader --ignore-platform-req=php

FROM node:16 AS build-frontend

WORKDIR /var/www
# To make tailwind purge find templates from vendor
COPY --from=build-php /var/www/vendor /var/www/vendor
COPY . ./

RUN yarn && yarn prod

FROM php:8.0-apache

ENV APACHE_DOCUMENT_ROOT /var/www/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
sed -ri -e 's!LogFormat\ "\%h\ \%l\ \%u\ \%t!LogFormat\ "\%h\ \%l\ \%u\ \%t\ \%D!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
echo "upload_max_filesize = 2G; " > /usr/local/etc/php/conf.d/upload.ini && \
echo "post_max_size = 2500M;" >> /usr/local/etc/php/conf.d/upload.ini && \
echo "date.timezone = \"Europe/Berlin\"" >> /usr/local/etc/php/conf.d/timezone.ini && \
echo 'memory_limit = 2G' >> /usr/local/etc/php/conf.d/max_memory.ini
RUN apt-get update && \
  apt-get install -y \
    git \
    curl libcurl3-dev \
    mariadb-client \
    libpng-dev libwebp-dev libjpeg62-turbo-dev libfreetype6-dev libpng-dev \
    libxpm-dev \
    zip libzip-dev \
    gnupg2 \
    sudo && \
  docker-php-ext-configure curl && \
  docker-php-ext-install mysqli curl exif && \
  docker-php-ext-configure zip && \
  docker-php-ext-install zip && \
  docker-php-ext-configure gd && \
  docker-php-ext-install gd && \
  docker-php-ext-install pdo pdo_mysql && \
  docker-php-ext-install opcache && \
  a2enmod rewrite && \
  a2enmod deflate

RUN pecl install -o -f redis && \
  rm -rf /tmp/pear && \
  docker-php-ext-enable redis

WORKDIR /var/www

COPY .docker/prod/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY . ./
COPY --from=build-frontend /var/www/public /var/www/public
COPY --from=build-php /var/www/vendor /var/www/vendor
RUN rm /var/www/node_modules -rf

ADD .docker/dev/setup.sh /usr/local/bin/setup
ADD .docker/prod/run.sh /
CMD /run.sh
