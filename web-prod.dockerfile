FROM nginx:1.10

ADD ./vhost.prod.conf /etc/nginx/conf.d/default.conf

ADD ./certs/star_certificate.crt /etc/nginx/star_certificate.crt
ADD ./certs/star_certificate.key /etc/nginx/star_certificate.key

COPY public /var/www/public
RUN chown -R www-data:www-data /var/www/public

RUN chmod 664 -R public
USER $USER

RUN chmod 664 -R /var/www/public
USER $USER

WORKDIR /var/www
