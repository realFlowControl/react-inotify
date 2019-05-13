<?php
declare(strict_types=1);

use React\EventLoop\Factory;
use Flowcontrol\React\Inotify\InotifyStream;

require __DIR__ . '/../vendor/autoload.php';

$loop = Factory::create();

$fd = inotify_init();
$watch_descriptor = inotify_add_watch($fd, __DIR__, IN_CLOSE_WRITE);

$watcher = new InotifyStream($fd, $loop);
$watcher->on('data', function (array $data) {
    var_dump($data);
});

$loop->run();
