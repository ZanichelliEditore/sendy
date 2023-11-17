FROM php:8.1-fpm

ARG USER
ARG UID

RUN apt-get update && apt-get install -y procps libmcrypt-dev mariadb-client openssl zip unzip git libfreetype6-dev libjpeg62-turbo-dev libgd-dev libpng-dev apt-utils libcurl4-openssl-dev pkg-config libssl-dev vim \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install gd \
    && pecl install xdebug\
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./custom.d /usr/local/etc/php/conf.d

RUN mkdir -p /home/$USER
RUN groupadd -g $UID $USER
RUN useradd -u $UID -g $USER $USER -d /home/$USER
RUN chown $USER:$USER /home/$USER

WORKDIR /var/www
USER $USER