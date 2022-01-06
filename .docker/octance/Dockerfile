FROM php:8.0-buster

RUN echo "upload_max_filesize = 2G; " > /usr/local/etc/php/conf.d/upload.ini && \
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
  docker-php-ext-install pcntl && \
  pecl install swoole && docker-php-ext-enable swoole

WORKDIR /var/www

ADD setup.sh /usr/local/bin/setup
ADD run.sh /
CMD /run.sh
