<?php

namespace Flowcontrol\React\Inotify\Tests;

use PHPUnit\Framework\TestCase;
use Flowcontrol\React\Inotify\InotifyStream;

class InotifyStreamTest extends TestCase
{
    protected $loop = null;

    public function setUp() : void
    {
        $this->loop = $this->getMockBuilder('\React\EventLoop\LoopInterface')->getMock();
    }

    /**
     * @covers \Flowcontrol\React\Inotify\InotifyStream::__construct
     */
    public function testInitWrongStream()
    {
        $this->expectException(\InvalidArgumentException::class);
        new InotifyStream(null, $this->loop);
    }
}
