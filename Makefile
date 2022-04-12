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
static-code-analysis: vendor ## Runs a static code analysis with phpstan/phpstan and vimeo/psalm
	#docker run -it --rm -v${PWD}:/opt/project -w /opt/project php:7.4 vendor/bin/phpstan --configuration=phpstan.neon
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project php:7.4 vendor/bin/psalm

.PHONY: test
test: ## Runs unit tests with phpunit/phpunit
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project php:7.4 vendor/bin/phpunit

.PHONY: dependency-analysis
dependency-analysis: vendor ## Runs a dependency analysis with maglnet/composer-require-checker
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project php:7.4 .phive/composer-require-checker check --config-file=/opt/project/composer-require-checker.json

vendor: composer.json composer.lock
	composer validate --strict
	composer install --no-interaction --no-progress

.PHONY: pre-commit-test
pre-commit-test: fix-code-style test code-style static-code-analysis

