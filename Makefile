PHP_BIN = docker run -it --rm -v${PWD}:/opt/project -w /opt/project php:7.4

.PHONY: help
help: ## Displays this list of targets with descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: code-style
code-style:
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project phpdoc/phpcs-ga:latest -d memory_limit=1024M -s

.PHONY: fix-code-style
fix-code-style:
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project phpdoc/phpcs-ga:latest phpcbf

.PHONY: static-code-analysis
static-code-analysis: vendor phpstan psalm ## Runs a static code analysis with phpstan/phpstan and vimeo/psalm

.PHONY: phpstan
phpstan:
	$(PHP_BIN) -d memory_limit=1024M vendor/bin/phpstan --configuration=phpstan.neon

.PHONY: psalm
psalm:
	$(PHP_BIN) vendor/bin/psalm --update-baseline

.PHONY: test
test: test-unit test-functional test-integration## Runs all test suites with phpunit/phpunit

.PHONY: test-unit
test-unit: ## Runs unit tests with phpunit/phpunit
	$(PHP_BIN) vendor/bin/phpunit --testsuite=unit

.PHONY: test-functional
test-functional: ## Runs functional tests with phpunit/phpunit
	$(PHP_BIN) vendor/bin/phpunit --testsuite=functional

.PHONY: test-integration
test-integration: ## Runs integration tests with phpunit/phpunit
	$(PHP_BIN) vendor/bin/phpunit --testsuite=integration

.PHONY: dependency-analysis
dependency-analysis: vendor ## Runs a dependency analysis with maglnet/composer-require-checker
	$(PHP_BIN) .phive/composer-require-checker check --config-file=/opt/project/composer-require-checker.json

vendor: composer.json composer.lock
	composer validate --no-check-publish
	composer install --no-interaction --no-progress  --ignore-platform-reqs

.PHONY: rector
rector: ## Refactor code using rector
	$(PHP_BIN) vendor/bin/rector process packages

.PHONY: pre-commit-test
pre-commit-test: fix-code-style test code-style static-code-analysis
