.PHONY: test all

all: vendor tools tags

vendor: composer.lock
	composer install -o

composer.lock: composer.json
	composer update -o

test:
	composer test

tools:
	phive install

tags: vendor src tests examples
	ctags -R --languages=PHP src/ tests/ examples/ vendor/
