FROM node:16-alpine

RUN npm cache clear --force
RUN npm config set unsafe-perm true
RUN npm install -g nodemon
RUN npm install -g lodash-cli
RUN npm install -g moment
RUN npm config set cache --global

RUN apk add --update \
    python3 \
    python3-dev \
    py3-pip \
    build-base \
    && pip3 install virtualenv \
    && rm -rf /var/cache/apk/*

WORKDIR /app

ONBUILD COPY . /app
ONBUILD RUN virtualenv /env && /env/bin/pip install -r /app/requirements.txt

RUN mkdir -p /var/www
WORKDIR /var/www

RUN chmod 664 -R /var/www/public/
USER $USER