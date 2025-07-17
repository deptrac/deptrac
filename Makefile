#!make

.PHONY: help build tests deptrac gpg
help: ## Displays list of available targets with their descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

PHP_VERSION = 81
CONTAINER = docker compose
CLI = $(CONTAINER) exec php$(PHP_VERSION)

COMPOSER = composer
COMPOSER_DEPENDENCY_ANALYSER = ./tools/dependency-analyser/bin/composer-dependency-analyser
PHP_CS_FIXER = ./tools/php-cs-fixer/bin/php-cs-fixer
PHPSTAN = ./tools/phpstan/bin/phpstan
PSALM = ./tools/psalm/bin/psalm
PHPUNIT = ./tools/phpunit/bin/phpunit -c .
INFECTION = ./tools/infection/bin/roave-infection-static-analysis-plugin

# container setup
up: ## Start all containers with docker compose up
	$(CONTAINER) up -d

down: ## Shutdown all containers with docker compose down
	$(CONTAINER) down --remove-orphans

restart: up down ## Restart all containers

update:
	$(CONTAINER) pull
	$(CONTAINER) build --build-arg UID=$(UID)
	$(CONTAINER) down
	$(CONTAINER) up -d

cli: up ## connect into container
	$(CLI) bash

cache-clear: ## clears cache
	rm -rf .cache/*

install: vendor ## Installs dependencies
vendor: composer.json composer.lock
	$(COMPOSER) install --no-interaction --no-progress --ansi

composer-dependency-analyser: install ## Performs static code analysis using composer-dependency-analyser
	$(COMPOSER_DEPENDENCY_ANALYSER)

deptrac: install ## Analyses own architecture using the default config confile
	./deptrac analyse -c deptrac.php --no-progress --ansi

infection: install ## Runs mutation tests
	$(INFECTION) --threads=$(shell nproc || sysctl -n hw.ncpu || 1) --test-framework-options='--testsuite=Tests' --only-covered --min-msi=85 --psalm-config=psalm.xml

php-cs-check: install ## Checks for code style violation
	$(PHP_CS_FIXER) fix --diff --using-cache=no --verbose --dry-run

cs: install ## Fixes any found code style violation
	$(PHP_CS_FIXER) fix

phpstan: install ## Performs static code analysis using phpstan
	$(PHPSTAN) analyse

psalm: install ## Performs static code analysis using psalm
	$(PSALM)

test: install ## run our testsuite
	$(PHPUNIT)

test-coverage: install ## Runs tests and generate an html coverage report
	XDEBUG_MODE=coverage $(PHPUNIT) --coverage-html coverage

tests: install ## Runs tests followed by a very basic e2e-test
	$(PHPUNIT)
	./deptrac analyse --config-file=docs/examples/Fixture.depfile.yaml --no-cache
