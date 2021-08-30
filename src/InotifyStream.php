<?php

declare(strict_types=1);

namespace Flowcontrol\React\Inotify;

use ErrorException;
use Evenement\EventEmitter;
use InvalidArgumentException;
use React\EventLoop\LoopInterface;
use RuntimeException;
use TypeError;
use function fclose;
use function function_exists;
use function get_resource_type;
use function inotify_add_watch;
use function inotify_init;
use function inotify_queue_len;
use function inotify_read;
use function inotify_rm_watch;
use function is_resource;
use function restore_error_handler;
use function set_error_handler;
use function stream_set_blocking;
use function stream_set_read_buffer;

final class InotifyStream extends EventEmitter
{
    /**
     * @var resource
     */
    private $inotify;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var string[]
     */
    private $watchers = [];

    /**
     * @psalm-suppress RedundantConditionGivenDocblockType
     * @psalm-suppress DocblockTypeContradiction
     */
    public function __construct(?LoopInterface $loop = null)
    {
        $inotify = inotify_init();

        if (
            !is_resource($inotify) ||
            get_resource_type($inotify) !== 'stream'
        ) {
            throw new InvalidArgumentException(
                'Call to `inotify_init()` did not succeed'
            );
        }

        // this class relies on non-blocking I/O in order to not interrupt
        // the event loop e.g. pipes on Windows do not support this:
        // https://bugs.php.net/bug.php?id=47918
        if (stream_set_blocking($inotify, false) !== true) {
            throw new RuntimeException(
                'Unable to set stream resource to non-blocking mode'
            );
        }

        // Use unbuffered read operations on the underlying stream resource.
        // Reading chunks from the stream may otherwise leave unread bytes in
        // PHP's stream buffers which some event loop implementations do not
        // trigger events on (edge triggered).
        // This does not affect the default event loop implementation (level
        // triggered), so we can ignore platforms not supporting this (HHVM).
        if (function_exists('stream_set_read_buffer')) {
            stream_set_read_buffer($inotify, 0);
        }

        $this->inotify = $inotify;
        $this->loop    = $loop ?? \React\EventLoop\Loop::get();

        $this->loop->addReadStream($this->inotify, [$this, 'handleData']);
    }

    public function __destruct()
    {
        $this->loop->removeReadStream($this->inotify);
        fclose($this->inotify);
        $this->emit('close');
        $this->removeAllListeners();
    }

    public function addWatch(string $path, int $mode): int
    {
        $wd                  = inotify_add_watch($this->inotify, $path, $mode);
        $this->watchers[$wd] = $path;

        return $wd;
    }

    public function rmWatch(int $wd): bool
    {
        unset($this->watchers[$wd]);

        return inotify_rm_watch($this->inotify, $wd);
    }

    /**
     * @internal
     */
    public function handleData(): void
    {
        /** @var null|ErrorException */
        $error = null;
        set_error_handler(static function (
            int $errno,
            string $errstr,
            string $errfile,
            int $errline
        ) use (&$error): bool {
            $error = new ErrorException(
                $errstr,
                0,
                $errno,
                $errfile,
                $errline
            );

            return true;
        });

        // fetch all events, as long as there are events in the queue
        $events = [];

        try {
            while (inotify_queue_len($this->inotify)) {
                $events[] = inotify_read($this->inotify);
            }
        } catch (TypeError $e) {
            $error = $e;
        }

        restore_error_handler();

        if ($error !== null) {
            $this->emit(
                'error',
                [
                    new RuntimeException(
                        'Unable to read from stream: ' . $error->getMessage(),
                        0,
                        $error
                    ),
                ]
            );

            return;
        }

        if (count($events)) {
            $this->emit('event', $events);
        }
    }
}
