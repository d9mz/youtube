FROM php:8.1.5-fpm-alpine3.15

RUN apk add --no-cache ffmpeg
RUN apk add --no-cache icu-dev
RUN docker-php-ext-install intl
RUN docker-php-ext-install opcache
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN apk add --no-cache autoconf alpine-sdk
RUN pecl install apcu
RUN docker-php-ext-enable apcu
RUN apk add --no-cache libzip-dev
RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip
RUN docker-php-ext-configure exif
RUN docker-php-ext-install exif
RUN docker-php-ext-enable exif

WORKDIR /var/www/symfony_docker

RUN apk add --no-cache bash
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/zmq
RUN git clone https://github.com/mkoppanen/php-zmq.git /var/www/zmq
RUN apk add --no-cache libzmq zeromq zeromq-dev
RUN phpize
RUN ./configure
RUN make
RUN make install
RUN docker-php-ext-enable zmq