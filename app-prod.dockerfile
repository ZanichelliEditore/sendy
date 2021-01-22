FROM php:7.4.2-fpm

RUN apt-get update && apt-get install -y procps libmcrypt-dev openssl zip unzip git libfreetype6-dev libjpeg62-turbo-dev libgd-dev libpng-dev apt-utils libcurl4-openssl-dev pkg-config libssl-dev vim \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

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

CMD ["/usr/bin/supervisord"]
