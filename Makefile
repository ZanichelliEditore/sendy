.PHONY: help up down shell npm_watch composer_update

ENV ?= dev
PROJECT ?= sendy

help:                             ## Show this help.
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'
up:                               ## Turn on container services
	docker-compose --file docker-compose.$(ENV).yml up -d
stop:                             ## Turn off container services
	docker-compose --file docker-compose.$(ENV).yml stop
down:                             ## Turn off and remove container services
	docker-compose --file docker-compose.$(ENV).yml down
build:                            ## Build container images
	docker-compose --file docker-compose.$(ENV).yml build
rebuild:                          ## Rebuild and turn on container services
	docker-compose --file docker-compose.$(ENV).yml up -d --build
shell:                            ## Open a shell con container app
	docker exec -it $(PROJECT)_app bash
shell_mongo:                            ## Open a shell con container app
	docker exec -it $(PROJECT)_mongo bash
composer_install:                  ## Execute composer install
	docker exec -it $(PROJECT)_app composer install
composer_update:                  ## Execute composer update
	docker exec -it $(PROJECT)_app composer update
run_tests:                        ## Execute phpunit
	docker exec -it $(PROJECT)_app vendor/bin/phpunit

.DEFAULT_GOAL := help
