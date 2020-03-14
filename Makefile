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
npm_watch:                        ## Execute npm run watch
	docker-compose --file docker-compose.$(ENV).yml run --rm nodejs npm run watch
npm_run:                          ## Execute npm run (prod or dev based on ENV param)
	docker-compose --file docker-compose.$(ENV).yml run --rm nodejs npm run $(ENV)
npm_install:                      ## Execute npm install package [use PACKAGE=<packageName>]
	docker-compose --file docker-compose.$(ENV).yml run --rm nodejs npm install $(PACKAGE)
shell:                            ## Open a shell con container app
	docker exec -it $(PROJECT)_app bash
shell_mongo:                      ## Open a shell con container app
	docker exec -it $(PROJECT)_mongo bash
composer_install:                 ## Execute composer install
	docker exec -it $(PROJECT)_app composer install
composer_update:                  ## Execute composer update
	docker exec -it $(PROJECT)_app composer update
run_tests:                        ## Execute phpunit
	docker exec -it $(PROJECT)_app vendor/bin/phpunit
run_tests_coverage:               ## Execute coverage phpunit
	docker exec $(PROJECT)_app vendor/bin/phpunit --coverage-html tmp/coverage

.DEFAULT_GOAL := help
