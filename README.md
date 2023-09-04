# flow-control/react-inotify

[![Build Status](https://img.shields.io/github/actions/workflow/status/flow-control/react-inotify/ci.yml?style=for-the-badge&logo=github%20actions&branch=master)](https://github.com/flow-control/react-inotify/actions)
[![Coverage](https://img.shields.io/codecov/c/github/flow-control/react-inotify?style=for-the-badge&logo=codecov)](https://codecov.io/gh/flow-control/react-inotify)
[![PHP Version](https://img.shields.io/packagist/php-v/flow-control/react-inotify.svg?style=for-the-badge)](https://github.com/flow-control/react-inotify)
[![Stable Version](https://img.shields.io/packagist/v/flow-control/react-inotify.svg?style=for-the-badge&label=Latest)](https://packagist.org/packages/flow-control/react-inotify)

Simple, async inotify event handler build with ReactPHP

This library is heavily inspired by [reactphp/stream](https://github.com/reactphp/stream).

## Dependencies

This library depends on the [PHP Inotify extension](https://pecl.php.net/package/inotify), available via PECL

```bash
$ pecl install inotify
```

## PHP Version Support

If you are looking for PHP 7 support you need to install `flow-control/react-inotify` in version 1. Version 2 dropped support for PHP 7.

## Usage

### Install

```bash
$ composer require flow-control/react-inotify
```

### How to use

Create an object from the `\Flowcontrol\React\Inotify\InotifyStream` class
and register your event handlers.

```php
$inotify = new \Flowcontrol\React\Inotify\InotifyStream();
$inotify->on('event', function (array $data) {
    var_dump($data);
});
$inotfiy->addWatch(__DIR__, IN_CLOSE_WRITE);
```

## Tests

```bash
$ composer install
$ composer test
```

## Build with

- [ReactPHP](https://reactphp.org/)
- [evenement/evenement](https://github.com/igorw/evenement)

## License

MIT, see [LICENSE file](LICENSE).
