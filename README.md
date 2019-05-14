# flow-control/react-inotify [![Build Status](https://api.travis-ci.com/flow-control/react-inotify.svg?branch=master)](https://travis-ci.com/flow-control/react-inotify)

Simple, async inotify event handler build with ReactPHP

This library is heavily inspired by reactphp/stream.

## Install

```bash
$ composer require flow-control/react-inotify
```

## Usage

### Low level interface

When using the low level interface, you need to setup the intofy resource
and add watcher as you need for yourself. You may then pass the stream on to
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

### High level interface

TBD

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
