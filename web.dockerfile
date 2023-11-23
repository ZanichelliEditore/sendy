FROM nginx:1.25

ADD ./sendy.vhost.dev.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www
