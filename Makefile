.PHONY: test

vendor: composer.lock
	composer install -o

composer.lock: composer.json
	composer update -o

test:
	composer test
