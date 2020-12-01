# flow-control/react-inotify

[![Build Status](https://img.shields.io/travis/com/flow-control/react-inotify/master.svg?style=for-the-badge&logo=travis)](https://travis-ci.com/flow-control/react-inotify)
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

## Usage

### Install

```bash
$ composer require flow-control/react-inotify
```

### How to use

You need to setup the inotify resource, and pass the valid handle to the
`\Flowcontrol\React\Inotify\InotifyStream` class and register your event
handlers.

```php
$fd = inotify_init();
$watch_descriptor = inotify_add_watch($fd, __DIR__, IN_CLOSE_WRITE);

$watcher = new \Flowcontrol\React\Inotify\InotifyStream($fd, $loop);
$watcher->on('event', function (array $data) {
    var_dump($data);
});

inotify_rm_watch($fd, $watch_descriptor);
fclose($fd);
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
