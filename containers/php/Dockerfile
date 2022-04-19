FROM php:8.1.5-fpm-alpine3.15

RUN apk add --no-cache icu-dev
RUN docker-php-ext-install intl opcache pdo pdo_mysql
RUN apk add --no-cache autoconf alpine-sdk
RUN pecl install apcu
RUN docker-php-ext-enable apcu
RUN apk add --no-cache libzip-dev
RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip

WORKDIR /var/www/symfony_docker

RUN apk add --no-cache bash
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony