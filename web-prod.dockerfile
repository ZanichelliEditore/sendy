FROM nginx:1.10

ADD ./vhost.prod.conf /etc/nginx/conf.d/default.conf

ADD ./certs/star_zanichelli_it.crt /etc/nginx/star_zanichelli_it.crt
ADD ./certs/star_zanichelli_it.key /etc/nginx/star_zanichelli_it.key

WORKDIR /var/www
