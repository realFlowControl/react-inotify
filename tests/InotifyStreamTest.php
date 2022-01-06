<?php

declare(strict_types=1);

namespace Flowcontrol\React\Inotify\Tests;

use const IN_CLOSE_WRITE;
use Flowcontrol\React\Inotify\InotifyStream;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;
use stdClass;
use TypeError;

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
        $watcher->on('close', $this->expectCallableOnce());
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

    public function testRemoveWatch(): void
    {
        $watcher = new InotifyStream();
        $wd      = $watcher->addWatch(__DIR__, IN_CLOSE_WRITE);
        $watcher->rmWatch($wd);
        $watcher->on('event', $this->expectCallableOnce());
        // it might seem odd, that there we expectCallableOnce, but you need
        // to know that calling `inotify_rm_watch()` emit a IN_IGNORED event
        touch(__DIR__ . '/testfileRmWatch');
        unlink(__DIR__ . '/testfileRmWatch');
        $watcher->handleData();
    }

    public function testErrorEmitOnInotifyProblem(): void
    {
        $watcher = new InotifyStream();
        $watcher->addWatch(__DIR__, IN_CLOSE_WRITE);
        $watcher->on('event', $this->expectCallableNever());
        /** @var RuntimeException */
        $e = null;
        $watcher->on('error', function ($data) use (&$e): void {
            $e = $data;
        });

        $prop = new ReflectionProperty("\Flowcontrol\React\Inotify\InotifyStream", 'inotify');
        $prop->setAccessible(true);
        /** @var resource */
        $inotify = $prop->getValue($watcher);
        fclose($inotify);

        touch(__DIR__ . '/testfile');
        unlink(__DIR__ . '/testfile');
        $watcher->handleData();

        $this->assertInstanceOf(
            RuntimeException::class,
            $e
        );

        $this->assertSame(
            0,
            $e->getCode()
        );

        $this->assertSame(
            'Unable to read from stream: inotify_queue_len(): supplied resource is not a valid stream resource',
            $e->getMessage()
        );

        $prev = $e->getPrevious();

        $this->assertInstanceOf(
            TypeError::class,
            $prev
        );
    }

    private function expectCallableNever()
    {
        $mock = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $mock->expects($this->never())
             ->method('__invoke');

        return $mock;
    }

    private function expectCallableOnce()
    {
        $mock = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $mock->expects($this->once())
             ->method('__invoke');

        return $mock;
    }

    private function expectCallableTimes(int $times)
    {
        $mock = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $mock->expects($this->exactly($times))
            ->method('__invoke');

        return $mock;
    }
}
