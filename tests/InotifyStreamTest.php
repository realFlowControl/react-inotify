<?php

namespace Flowcontrol\React\Inotify\Tests;

use PHPUnit\Framework\TestCase;
use Flowcontrol\React\Inotify\InotifyStream;

class InotifyStreamTest extends TestCase
{

    /**
     * @covers \Flowcontrol\React\Inotify\InotifyStream::__construct
     */
    public function testInitNoStream(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        $this->expectException(\InvalidArgumentException::class);
        new InotifyStream(null, $loop);
    }

    /**
     * @covers \Flowcontrol\React\Inotify\InotifyStream::__construct
     */
    public function testInitStreamNotInReadMode(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        $this->expectException(\InvalidArgumentException::class);
        $fd = fopen(__FILE__, 'a');
        new InotifyStream($fd, $loop);
    }

    /**
     * @covers \Flowcontrol\React\Inotify\InotifyStream::__construct
     * @covers \Flowcontrol\React\Inotify\InotifyStream::resume
     * @covers \Flowcontrol\React\Inotify\InotifyStream::handleData
     */
    public function testValidStreamWithoutEvent(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        $fd = inotify_init();
        inotify_add_watch($fd, __DIR__, IN_CLOSE_WRITE);
        $watcher = new InotifyStream($fd, $loop);
        $watcher->on('event', $this->expectCallableNever());
        $watcher->handleData();
        fclose($fd);
    }

    /**
     * @covers \Flowcontrol\React\Inotify\InotifyStream::__construct
     * @covers \Flowcontrol\React\Inotify\InotifyStream::resume
     * @covers \Flowcontrol\React\Inotify\InotifyStream::handleData
     */
    public function testValidStreamWithEvent(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        $fd = inotify_init();
        inotify_add_watch($fd, __DIR__, IN_CLOSE_WRITE);
        $watcher = new InotifyStream($fd, $loop);
        $watcher->on('event', $this->expectCallableOnce());
        touch(__DIR__.'/testfile');
        unlink(__DIR__.'/testfile');
        $watcher->handleData();
        fclose($fd);
    }

    /**
     * @covers \Flowcontrol\React\Inotify\InotifyStream::__construct
     * @covers \Flowcontrol\React\Inotify\InotifyStream::resume
     * @covers \Flowcontrol\React\Inotify\InotifyStream::handleData
     * @covers \Flowcontrol\React\Inotify\InotifyStream::pause
     * @covers \Flowcontrol\React\Inotify\InotifyStream::close
     * @covers \Flowcontrol\React\Inotify\InotifyStream::isReadable
     */
    public function testValidStreamWithoutEventAndClose(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        $fd = inotify_init();
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
        $this->assertFalse($watcher->isReadable());
    }

    /**
     * @covers \Flowcontrol\React\Inotify\InotifyStream::__construct
     * @covers \Flowcontrol\React\Inotify\InotifyStream::resume
     * @covers \Flowcontrol\React\Inotify\InotifyStream::handleData
     * @covers \Flowcontrol\React\Inotify\InotifyStream::pause
     * @covers \Flowcontrol\React\Inotify\InotifyStream::close
     * @covers \Flowcontrol\React\Inotify\InotifyStream::isReadable
     */
    public function testCloseStreamWhileHandling(): void
    {
        /** @var \React\EventLoop\LoopInterface */
        $loop = $this->getMockBuilder(\React\EventLoop\LoopInterface::class)->getMock();
        $fd = inotify_init();
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
        $mock = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $mock->expects($this->never())->method('__invoke');
        return $mock;
    }

    private function expectCallableOnce()
    {
        $mock = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $mock->expects($this->once())->method('__invoke');
        return $mock;
    }
}
