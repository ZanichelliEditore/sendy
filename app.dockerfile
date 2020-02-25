FROM php:7-fpm

RUN apt-get update && apt-get install -y procps libmcrypt-dev openssl zip unzip git libpng-dev libjpeg62-turbo-dev libgd-dev apt-utils libcurl4-openssl-dev pkg-config libssl-dev vim \
    && docker-php-ext-configure gd \
    && docker-php-ext-install gd \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./custom.d /usr/local/etc/php/conf.d

ADD ./ /var/www
RUN mkdir -p /var/www/vendor
RUN chown -R www-data:www-data /var/www
RUN chmod 777 -R /var/www/storage
RUN chmod 777 -R /var/www/vendor
WORKDIR /var/www

USER www-data



