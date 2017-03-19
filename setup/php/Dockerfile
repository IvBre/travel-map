FROM php:7.0-fpm

COPY ./php.ini /usr/local/etc/php/

RUN pecl install xdebug-2.5.0 \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install pdo_mysql