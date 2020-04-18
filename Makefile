.PHONY: clean code-style coverage help test test-unit test-integration static-analysis install-dependencies xdebug-enable xdebug-disable fixtures-load
.DEFAULT_GOAL := test

PHPUNIT = DATABASE_URL=${DATABASE_URL_TEST} ./vendor/bin/phpunit -c ./phpunit.xml --no-coverage
PHPSPEC = ./vendor/bin/phpspec run  -c ./phpspec.yml --format dot -vvv --no-interaction
PHPSTAN = ./vendor/bin/phpstan
PHPCS = ./vendor/bin/phpcs
CONSOLE = ./bin/console

clean:
	rm -rf ./build ./vendor

code-style:
	mkdir -p build/logs/phpcs
	${PHPCS}

coverage:
	php -dpcov.enabled=1 -dpcov.directory=./src ${PHPSPEC}
	./vendor/bin/coverage-check build/logs/phpspec/coverage/coverage.xml --only-percentage

test:
	${PHPSPEC} --no-coverage
	${PHPUNIT}

test-unit:
	${PHPSPEC} --no-coverage
	${PHPUNIT} --group=Unit

test-integration:
	${PHPUNIT} --group=Integration

static-analysis:
	${PHPSTAN} analyse

xdebug-enable:
	php-ext-enable xdebug

xdebug-disable:
	php-ext-disable xdebug

fixtures-load:
	${CONSOLE} mitra:fixtures:load ./fixtures

help:
	# Usage:
	#   make <target> [OPTION=value]
	#
	# Targets:
	#   clean                     Cleans the coverage and the vendor directory
	#   code-style                Check codestyle using phpcs
	#   coverage                  Generate code coverage (html, clover)
	#   fixtures-load             Load fixtures
	#   help                      You're looking at it!
	#   test (default)            Run all the tests with phpunit
	#   test-unit                 Run all unit tests with phpunit
	#   test-integration          Run all integration tests with phpunit
	#   static-analysis           Run static analysis using phpstan
	#   xdebug-enable             Enable xdebug
	#   xdebug-disable            Disable xdebug
