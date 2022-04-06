.PHONY: code-style
code-style:
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project phpdoc/phpcs-ga:latest -d memory_limit=1024M -s

.PHONY: fix-code-style
fix-code-style:
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project phpdoc/phpcs-ga:latest phpcbf

.PHONY: phpstan
phpstan:
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project php:7.4 vendor/bin/phpstan analyse src --no-progress --level max --configuration phpstan.neon

.PHONY: psaml
psalm:
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project php:7.4 vendor/bin/psalm

.PHONY: test
test:
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project php:7.4 vendor/bin/phpunit

.PHONY: dependency-analysis
dependency-analysis: vendor ## Runs a dependency analysis with maglnet/composer-require-checker
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project php:7.4 .phive/composer-require-checker check --config-file=/opt/project/composer-require-checker.json

.PHONY: pre-commit-test
pre-commit-test: fix-code-style test code-style phpstan psalm

