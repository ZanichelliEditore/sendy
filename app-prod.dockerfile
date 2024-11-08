FROM php:8.1-fpm

RUN apt-get update && apt-get install -y procps libmcrypt-dev openssl zip unzip git libfreetype6-dev  mariadb-client libjpeg62-turbo-dev libgd-dev libpng-dev apt-utils libcurl4-openssl-dev pkg-config libssl-dev vim \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo_mysql opcache

RUN apt-get install -y supervisor
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./custom.d /usr/local/etc/php/conf.d

COPY ./ /var/www
RUN chown -R www-data:www-data \
    /var/www \
    /var/www/storage \
    /var/www/vendor

WORKDIR /var/www
USER www-data
