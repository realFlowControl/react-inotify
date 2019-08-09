# flow-control/react-inotify [![Build Status](https://api.travis-ci.com/flow-control/react-inotify.svg?branch=master)](https://travis-ci.com/flow-control/react-inotify)

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
