<?php

/**
 * start this example using: php 01-example.php
 * open another terminal and: touch foobar
 */

declare(strict_types=1);

use Flowcontrol\React\Inotify\InotifyStream;
use React\EventLoop\Factory;

require __DIR__ . '/../vendor/autoload.php';

$loop = Factory::create();

$watcher = new InotifyStream();
$watcher->addWatch(__DIR__, IN_CLOSE_WRITE);
$watcher->on('event', static function (array $data): void {
    print_r($data);
});

$loop->run();
