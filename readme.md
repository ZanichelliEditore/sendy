# About the project

This service is used to send emails.

# Requirements

View Docker and Docker-compose documentations:

    - https://docs.docker.com

    - https://docs.docker.com/compose/

# Requirements Setup

## Docker: Starting and stopping containers

Build the Docker images with `docker-compose up --build -d`.

Once created, the containers can be **started** anytime with the following command:

    docker-compose up -d

To **stop** the containers, use instead:

    docker-compose stop

Enter in the container (with the command above) and run the next commands inside it:

docker exec -it sendy_app_1 bash

Then follow the step **3 and 4** described after.

# Application Setup

These are the instructions to follow to set up the project on your local environment.

1.  Git clone the repository into your folder.

2.  Copy env.example to .env

3.  Install the required dependencies with composer

        composer install

4.  Generate a random application key

        php artisan key:generate

5.  Generate passport credentials

        php artisan passport:install

## Launch application without using Docker

    php artisan serve

# Accessing services

- **Web**: http://localhost:8743

- **DB**: enter in the container of mongo and run:

  mongo -u "user" -p

  credentials to use are in .env file: MONGO_INITDB_ROOT_USERNAME and MONGO_INITDB_ROOT_PASSWORD

  Other utils commands:

        show dbs

        use "DB"

        db.emails.find()
