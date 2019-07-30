.PHONY: test all

all: vendor tags

vendor: composer.lock
	composer install -o

composer.lock: composer.json
	composer update -o

test:
	composer test

tags: vendor src tests examples
	ctags -R --languages=PHP src/ tests/ examples/ vendor/
