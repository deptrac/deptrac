#!make

.PHONY: help build tests deptrac gpg
help: ## Displays list of available targets with their descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

PHP_VERSION = 82
CONTAINER = docker compose

COMPOSER = composer
COMPOSER_DEPENDENCY_ANALYSER = ./tools/dependency-analyser/bin/composer-dependency-analyser
MAGO = ./vendor/bin/mago
PHPSTAN = ./tools/phpstan/bin/phpstan
PHPUNIT = ./tools/phpunit/bin/phpunit -c .
INFECTION = ./tools/infection/bin/roave-infection-static-analysis-plugin
RECTOR = ./tools/rector/bin/rector

update:
	$(CONTAINER) build --pull --build-arg UID=$(UID)

cli: ## connect into container
	$(CONTAINER) run --rm php$(PHP_VERSION)

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
	$(MAGO) format --check

php-cs-fix: install ## Fixes any found code style violation
	$(MAGO) format

phpstan: install ## Performs static code analysis using phpstan
	$(PHPSTAN) analyse

rector-check: install ## Checks for automated code refactoring using rector
	$(RECTOR) process --dry-run

rector: install ## Performs automated code refactoring using rector
	$(RECTOR) process

test: install ## run our testsuite
	$(PHPUNIT)

test-coverage: install ## Runs tests and generate an html coverage report
	XDEBUG_MODE=coverage $(PHPUNIT) --coverage-html coverage

tests: install ## Runs tests followed by a very basic e2e-test
	$(PHPUNIT)
	./deptrac analyse --config-file=docs/examples/Fixture.depfile.yaml --no-cache

qa: php-cs-check composer-dependency-analyser phpstan deptrac tests infection ## runs all qa tools
