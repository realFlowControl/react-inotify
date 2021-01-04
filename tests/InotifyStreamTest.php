<?php

declare(strict_types=1);

namespace Flowcontrol\React\Inotify\Tests;

use Flowcontrol\React\Inotify\InotifyStream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Flowcontrol\React\Inotify\InotifyStream
 */
class InotifyStreamTest extends TestCase
{
    public function testInitNoStream(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        $this->expectException(InvalidArgumentException::class);
        new InotifyStream(null, $loop);
    }

    public function testInitStreamNotInReadMode(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        $this->expectException(InvalidArgumentException::class);
        $fd = fopen(__FILE__, 'a');
        new InotifyStream($fd, $loop);
    }

    public function testValidStreamWithoutEvent(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        $fd   = inotify_init();
        inotify_add_watch($fd, __DIR__, IN_CLOSE_WRITE);
        $watcher = new InotifyStream($fd, $loop);
        $watcher->on('event', $this->expectCallableNever());
        $watcher->on('close', $this->expectCallableOnce());
        $watcher->handleData();
        $watcher->close();
        fclose($fd);
    }

    public function testValidStreamWithEvent(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        $fd   = inotify_init();
        inotify_add_watch($fd, __DIR__, IN_CLOSE_WRITE);
        $watcher = new InotifyStream($fd, $loop);
        $watcher->on('event', $this->expectCallableOnce());
        touch(__DIR__ . '/testfile');
        unlink(__DIR__ . '/testfile');
        $watcher->handleData();
        fclose($fd);
    }

    public function testNoResumeAfterClose(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        $fd   = inotify_init();
        inotify_add_watch($fd, __DIR__, IN_CLOSE_WRITE);
        $watcher = new InotifyStream($fd, $loop);
        $watcher->on('event', $this->expectCallableOnce());
        touch(__DIR__ . '/testfile');
        unlink(__DIR__ . '/testfile');
        $watcher->handleData();
        $watcher->close();
        fclose($fd);
        $watcher->resume();
        $this->assertFalse($watcher->isReadable());
    }

    public function testValidStreamPauseWithEvent(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        $loop->expects($this->exactly(2))->method('removeReadStream');
        $loop->expects($this->exactly(2))->method('addReadStream');
        $fd   = inotify_init();
        inotify_add_watch($fd, __DIR__, IN_CLOSE_WRITE);
        $watcher = new InotifyStream($fd, $loop);
        $watcher->on('event', $this->expectCallableTimes(2));
        touch(__DIR__ . '/testfile');
        unlink(__DIR__ . '/testfile');
        $watcher->handleData();
        $watcher->pause();
        $watcher->resume();
        touch(__DIR__ . '/testfile');
        unlink(__DIR__ . '/testfile');
        $watcher->handleData();
        $watcher->close();
        fclose($fd);
    }

    public function testValidStreamWithoutEventAndClose(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        $fd   = inotify_init();
        inotify_add_watch($fd, __DIR__, IN_CLOSE_WRITE);
        $watcher = new InotifyStream($fd, $loop);
        $this->assertTrue($watcher->isReadable());
        $watcher->on('event', $this->expectCallableNever());
        $this->assertTrue($watcher->isReadable());
        $watcher->handleData();
        $this->assertTrue($watcher->isReadable());
        $watcher->close();
        $this->assertFalse($watcher->isReadable());
        // test close call on closed stream
        $watcher->close();
        fclose($fd);
        $this->assertFalse($watcher->isReadable());
    }

    public function testCloseStreamWhileHandling(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        $fd   = inotify_init();
        inotify_add_watch($fd, __DIR__, IN_CLOSE_WRITE);
        $watcher = new InotifyStream($fd, $loop);
        $this->assertTrue($watcher->isReadable());
        $watcher->on('event', $this->expectCallableNever());
        $watcher->on('error', $this->expectCallableOnce());
        fclose($fd);
        $watcher->handleData();
        $this->assertFalse($watcher->isReadable());
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

    private function expectCallableTimes(int $times)
    {
        $mock = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();
        $mock->expects($this->exactly($times))->method('__invoke');

        return $mock;
    }
}
