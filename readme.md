[![Build Status](https://travis-ci.org/ZanichelliEditore/sendy.svg?branch=master)](https://travis-ci.org/ZanichelliEditore/sendy)
[![codecov](https://codecov.io/gh/ZanichelliEditore/sendy/branch/master/graph/badge.svg)](https://codecov.io/gh/ZanichelliEditore/sendy)

# Sendy: delivery messages

This service is used to send emails from any applications without setting configurations.

# Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.
The project uses Docker systems to setup the environment and are provided docker files for both develop and production purpose.
See deployment for notes on how to deploy the project on a live system.

- [Prerequisites](#prerequisites)
- [Application Setup](#application-setup)
- [Installing](#installing)
- [Testing](#testing)
- [Deployment](#deployment)
- [Usage](#usage)
- [Appendix](#appendix)

## Prerequisites

- [Docker](https://docs.docker.com)
- [Docker Compose](https://docs.docker.com/compose/)

# Application Setup

## Docker: Starting and stopping containers

Build the Docker images with `docker-compose --file docker-compose.dev.yml up --build -d` using the command inside the project folder.

Once created, the containers can be **started** anytime with the following command:

    docker-compose --file docker-compose.dev.yml up -d

To **stop** the containers, use instead:

    docker-compose --file docker-compose.dev.yml stop

**_Notes:_** _In this case the `--file` parameter is mandatory because the default docker-compose filename has been changed._

_Launch `--file docker-compose.prod.yml` to run in production mode._

# Installing

All the installation steps have to be executed inside docker app container since it represents our project environment.

1.  Git clone the repository into your folder.

        git clone https://github.com/ZanichelliEditore/sendy.git

2)  Copy env.example to .env

3)  Start the containers and enter into the container app:

        docker-compose --file docker-compose.dev.yml up -d
        docker exec -it sendy_app bash

4)  Install the required dependencies with composer

        composer install

5)  Generate a random application key

        php artisan key:generate

6)  Generate passport credentials

        php artisan passport:install

## Testing

The project provides phpunit tests that can be launched using inside the container.
Verify project integrity launching tests (inside the container):

        vendor/bin/phpunit tests

or if you want to see the tests coverage:

        vendor/bin/phpunit --coverage-html tmp/coverage

## Deployment

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
from requests_oauthlib import OAuth2

auth = OAuth2(
    <client_id>,
    token={
        'access_token': <access_token>,
        'token_type': 'Bearer'
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
res = requests.post("https://sendyurl.com/api/v1/emails", params=params auth=auth)
if res.status_code != 200:
    # Failure status

data = res.json # get data as object using attribute .json
```

---

## Appendix

- **Web:**

  - the application server will run in http://localhost:8083
  - the documentation will be automatically generated in http://localhost:8083/documentation

- **Database:** sendy uses MongoDB as database to store both credentials and jobs for the email.

  You can query the database using the container of mongo and enter as verified user:

        docker exec -it sendy_mongo bash
        mongo -u "user" -p

  credentials to use are in .env file:

  ```php
  MONGO_INITDB_ROOT_USERNAME=
  MONGO_INITDB_ROOT_PASSWORD=
  ```

  Other mongo utils commands:

  ```php
  show dbs # show existing databases
  use DBNAME # select database

  show collections # show db collections
  db.emails.find() # select all documents in email collection
  ```

- **Docker:** the project provides useful shortcut commands to interact with docker.
  All the commands are created inside the `Makefile`.
  List all command using helper:

        make help

  By default the make command will launch development environment; anyway you can overwrite the env setting using production:

        make build ENV=prod
