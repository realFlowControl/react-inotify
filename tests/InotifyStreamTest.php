<?php

declare(strict_types=1);

namespace Flowcontrol\React\Inotify\Tests;

use const IN_CLOSE_WRITE;
use Flowcontrol\React\Inotify\InotifyStream;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Flowcontrol\React\Inotify\InotifyStream
 */
class InotifyStreamTest extends TestCase
{
    protected function setUp(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        \React\EventLoop\Loop::set(
            $loop
        );
    }

    public function testValidStreamWithoutEvent(): void
    {
        $watcher = new InotifyStream();
        $watcher->addWatch(__DIR__, IN_CLOSE_WRITE);
        $watcher->on('event', $this->expectCallableNever());
        $watcher->handleData();
    }

    public function testValidStreamWithEvent(): void
    {
        $watcher = new InotifyStream();
        $watcher->addWatch(__DIR__, IN_CLOSE_WRITE);
        $watcher->on('event', $this->expectCallableOnce());
        touch(__DIR__ . '/testfile');
        unlink(__DIR__ . '/testfile');
        $watcher->handleData();
    }

    private function expectCallableNever()
    {
        $mock = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();
        $mock->expects($this->never())->method('__invoke');

        return $mock;
    }

    private function expectCallableOnce()
    {
        $mock = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();
        $mock->expects($this->once())->method('__invoke');

        return $mock;
    }
}
