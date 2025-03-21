FROM nginx:1.26.3

ADD ./vhost.prod.conf /etc/nginx/conf.d/default.conf

ADD ./certs/star_certificate.crt /etc/nginx/star_certificate.crt
ADD ./certs/star_certificate.key /etc/nginx/star_certificate.key

COPY public /var/www/public
WORKDIR /var/www
