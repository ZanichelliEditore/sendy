FROM nginx:1.26.3

ADD ./sendy.vhost.dev.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www
