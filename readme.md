[![Build Status](https://travis-ci.org/ZanichelliEditore/sendy.svg?branch=master)](https://travis-ci.org/ZanichelliEditore/sendy)
[![codecov](https://codecov.io/gh/ZanichelliEditore/sendy/branch/master/graph/badge.svg)](https://codecov.io/gh/ZanichelliEditore/sendy)

# Sendy: delivery messages

This service is used to send emails from any applications without setting configurations.

# Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.
The project uses Docker systems to setup the environment and are provided docker files for both develop and production purpose.
See deployment for notes on how to deploy the project on a live system.

- [Prerequisites](#prerequisites)
- [Installing](#installing)
- [Configure](#Configure)
- [Application Setup](#application-setup)
- [Testing](#testing)
- [Deployment](#deployment)
- [Usage](#usage)
- [Appendix](#appendix) 

## Prerequisites

- [Docker](https://docs.docker.com)
- [Docker Compose](https://docs.docker.com/compose/)

# Application Setup

# Installing

Steps from 4 to 6 have to be executed inside docker app container since it represents our project environment.

1.  Git clone the repository into your folder.

    git clone https://github.com/ZanichelliEditore/sendy.git

2.  Copy env.example to .env

3.  Start the containers and enter into the container app:

    docker-compose --file docker-compose.dev.yml up -d
    docker exec -it sendy_app bash

4.  Install the required dependencies with composer.

    composer install

5.  Generate a random application key

    php artisan key:generate

6.  Generate passport credentials

    php artisan passport:install

7.  Activate queue worker

    php artisan queue:work

    **Note:** In production environment there is supervisord service and the queue is already activated when docker is up.

## Configure

By default email are logged into /storage/logs/laravel-day.log. To configure your SMTP server follow this guide https://laravel.com/docs/7.x/mail

## Docker: Starting and stopping containers

Build the Docker images with `docker-compose --file docker-compose.dev.yml up --build -d` using the command inside the project folder.

Once created, the containers can be **started** anytime with the following command:

    docker-compose --file docker-compose.dev.yml up -d

To **stop** the containers, use instead:

    docker-compose --file docker-compose.dev.yml stop

**_Notes:_** _In this case the `--file` parameter is mandatory because the default docker-compose filename has been changed._

_Launch `--file docker-compose.prod.yml` to run in production mode._

## Testing

The project provides phpunit tests that can be launched using inside the container.
Verify project integrity launching tests (inside the container):

    vendor/bin/phpunit tests

or if you want to see the tests coverage:

    vendor/bin/phpunit --coverage-html tmp/coverage

## Deployment

In order to define a production environment, we provide specific docker files and Ansible with Jenkins to define a process of a Continuous Deployment.

In production environment has to be set ssl certificates and the environment your project will use; edit the following files defining the right setup for your application:

- [ngnix-prod-conf](vhost.prod.conf), edit lines 8-9:

```
    ssl_certificate <your-certificate-file>;
    ssl_certificate_key <your-certificate-key-file>;
```

- [jenkinsfile](Jenkinsfile), set environment credentials; edit lines 16-19 and 26-28:

```php
    DB_DATABASE = "sendy"
    DB_USERNAME = <username>
    DB_PASSWORD = credentials(<db_pwd>)
    ...
    ..credentialsId: <certificate>..
    ..credentialsId: <certificate_key>..
```

- [inventory](ansible/inventory/production.inv), create a ~/.ssh/config file (into the server) and define the host as named in the inventory, `dc-php-prod`, e.g.:

```
Host dc-php7-prod
    HostName <ip-host>
    User <user>
```

## Usage

After the application has been deployed in one server and it became reachable through an endpoint, you can call the sendy api passin your data and authenticating through OAuth2 system.
Below there are some example in different languages:

### PHP

---

We will use [GuzzleHttp](http://docs.guzzlephp.org/en/stable/) to make easier http requests.

Retrieve the token for Oauth2 authentication:

```php

$client = new GuzzleHttp\Client();
$response = $client->post('https://sendyurl.com/oauth/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => env('CLIENT_ID'),
                'client_secret' => env('CLIENT_SECRET'),
                'scope' => '',
            ],
        ]);

$token = json_decode((string) $response->getBody(), true)['access_token'];
```

then call the api using the Bearer token:

```php
$res = $client->request('POST', 'https://sendyurl.com/api/v1/emails',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'body' => json_encode(
                    [
                        'to' => [
                            'email@example.com'
                        ],
                        'from' => 'origin@example.com',
                        'subject' => 'Test',
                        'body' => $body

                    ]
                )
            ]);
return json_decode((string) $response->getBody(), true)['message'];
```

### Python

---

The most used library to call api is [`requests`](https://pypi.org/project/requests/).
About oauth2 authentication there are several libraries, the most used are [`requests_oauthlib`](https://pypi.org/project/requests-oauthlib/) and [`oauthlib`](https://pypi.org/project/oauthlib/).

```python
import requests
from requests_oauthlib import OAuth2, OAuth2Session
from oauthlib.oauth2 import BackendApplicationClient


client_id = <client_id>
client_secret = <client_secret>
token_url = 'https://sendyurl.com/oauth/token'

client = BackendApplicationClient(client_id=client_id)
oauth = OAuth2Session(client=client)
token = oauth.fetch_token(token_url=token_url, client_id=client_id,
        client_secret=client_secret)

auth = OAuth2(
    client_id,
    token={
        'access_token': token['access_token'],
        'token_type': token['token_type'] # Bearer
    }
)

params = {
    'to': [
        'email@example.com'
    ],
    'from': 'origin@example.com',
    'subject': 'Test',
    'body': body
}
headers = {'Content-type': 'application/json', 'Accept': 'application/json'}
res = requests.post("https://sendyurl.com/api/v1/emails", headers=headers, json=params, auth=auth)
if res.status_code != 200:
    # Failure status

data = res.json # get data as object using attribute .json, or use r.content
```

---

## Appendix

- **Web:**

  - the application server will run in http://localhost:8083
  - the documentation will be automatically generated in http://localhost:8083/documentation

- **Database:** sendy uses mysql as database to store both credentials and jobs for the email.

  You can query the database using phpmyadmin

  credentials to use are in .env file:

  ```php
  DB_USERNAME=
  DB_PASSWORD=
  ```

- **Docker:** the project provides useful shortcut commands to interact with docker.
  All the commands are created inside the `Makefile`.
  List all command using helper:

        make help

  By default the make command will launch development environment; anyway you can overwrite the env setting using production:

        make build ENV=prod
