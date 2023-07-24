PHP_BIN = docker run -it --rm --user $$(id -u):$$(id -g) -v${PWD}:/opt/project -w /opt/project php:8.2-cli php -d memory_limit=1024M

.PHONY: help
help: ## Displays this list of targets with descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: code-style
code-style:
	$(PHP_BIN) vendor/bin/phpcs

.PHONY: fix-code-style
fix-code-style:
	$(PHP_BIN) vendor/bin/phpcbf

.PHONY: static-code-analysis
static-code-analysis: vendor phpstan psalm test-architecture ## Runs a static code analysis with phpstan/phpstan and vimeo/psalm

.PHONY: phpstan
phpstan:
	$(PHP_BIN) -d memory_limit=1024M vendor/bin/phpstan --configuration=phpstan.neon

.PHONY: psalm
psalm:
	$(PHP_BIN) vendor/bin/psalm --update-baseline

.PHONY: test
test: test-unit test-functional test-integration test-docs## Runs all test suites with phpunit/phpunit

.PHONY: test-unit
test-unit: ## Runs unit tests with phpunit/phpunit
	$(PHP_BIN) vendor/bin/phpunit --testsuite=unit

.PHONY: test-functional
test-functional: ## Runs functional tests with phpunit/phpunit
	$(PHP_BIN) vendor/bin/phpunit --testsuite=functional

.PHONY: test-integration
test-integration: ## Runs integration tests with phpunit/phpunit
	$(PHP_BIN) vendor/bin/phpunit --testsuite=integration

.PHONY: test-docs
test-docs: ## Generate projects docs without warnings
	$(PHP_BIN) vendor/bin/guides -vvv --no-progress docs /tmp/test --fail-on-log

.PHONY: cleanup
cleanup: cleanup-tests cleanup-build cleanup-cache

.PHONY: cleanup-tests
cleanup-tests: ## Cleans up temp directories created by test-integration
	@find ./tests -type d -name 'temp' -exec sudo rm -rf {} \;

.PHONY: cleanup-build
cleanup-build:
	@sudo rm -rf .build

.PHONY: cleanup-cache
cleanup-cache:
	@sudo rm -rf .phpunit.cache

.PHONY: test-architecture
test-architecture: vendor ## Runs deptrac to enfore architecural rules
	$(PHP_BIN) ./vendor/bin/deptrac --config-file deptrac.packages.yaml

vendor: composer.json composer.lock
	composer validate --no-check-publish
	composer install --no-interaction --no-progress  --ignore-platform-reqs

.PHONY: rector
rector: ## Refactor code using rector
	$(PHP_BIN) vendor/bin/rector process packages

.PHONY: pre-commit-test
pre-commit-test: fix-code-style test code-style static-code-analysis
