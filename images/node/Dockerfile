FROM node:10.16-alpine

RUN npm config set unsafe-perm true
RUN npm install -g yarn
RUN npm install -g nodemon
RUN npm install -g lodash-cli
RUN npm install -g moment

RUN apk add --update \
    python \
    python-dev \
    py-pip \
    build-base \
    && pip install virtualenv \
    && rm -rf /var/cache/apk/*

WORKDIR /app

ONBUILD COPY . /app
ONBUILD RUN virtualenv /env && /env/bin/pip install -r /app/requirements.txt

RUN mkdir -p /var/www
VOLUME ["/var/www"]

WORKDIR /var/www
