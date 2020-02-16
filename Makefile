.PHONY: clean code-style coverage help test test-unit test-integration static-analysis install-dependencies xdebug-enable xdebug-disable
.DEFAULT_GOAL := test

PHPUNIT = APP_ENV=test APP_DEBUG=false ./vendor/bin/phpunit -c ./phpunit.xml --no-coverage
PHPSPEC = APP_ENV=test APP_DEBUG=false phpdbg -qrr ./vendor/bin/phpspec run  -c ./phpspec.yml --no-coverage \
          --format dot -vvv --no-interaction
PHPUNIT_COV = APP_ENV=test APP_DEBUG=false phpdbg -qrr ./vendor/bin/phpunit -c ./phpunit.xml
PHPSPEC_COV = APP_ENV=test APP_DEBUG=false phpdbg -qrr ./vendor/bin/phpspec run -c ./phpspec.yml \
              --format dot -vvv --no-interaction
PHPSTAN = ./vendor/bin/phpstan
PHPCS = ./vendor/bin/phpcs
CONSOLE = ./bin/console

clean:
	rm -rf ./build ./vendor

code-style:
	mkdir -p build/logs/phpcs
	${PHPCS}

coverage:
	${PHPSPEC_COV}
	${PHPUNIT_COV}
	phpdbg -qrr ./vendor/bin/phpcov merge --clover build/logs/phpunit/junit.xml --html build/logs/phpunit/coverage \
	--text --ansi /tmp/coverage

test:
	${PHPSPEC}
	${PHPUNIT}

test-unit:
	${PHPSPEC}
	${PHPUNIT} --group=Unit

test-integration:
	${PHPUNIT} --group=Integration

static-analysis:
	${PHPSTAN} analyse

xdebug-enable:
	sudo php-ext-enable xdebug

xdebug-disable:
	sudo php-ext-disable xdebug

help:
	# Usage:
	#   make <target> [OPTION=value]
	#
	# Targets:
	#   clean                     Cleans the coverage and the vendor directory
	#   code-style                Check codestyle using phpcs
	#   coverage                  Generate code coverage (html, clover)
	#   help                      You're looking at it!
	#   test (default)            Run all the tests with phpunit
	#   test-unit                 Run all unit tests with phpunit
	#   test-integration          Run all integration tests with phpunit
	#   static-analysis           Run static analysis using phpstan
	#   xdebug-enable             Enable xdebug
	#   xdebug-disable            Disable xdebug
