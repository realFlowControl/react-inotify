<?php

declare(strict_types=1);

namespace Flowcontrol\React\Inotify;

use ErrorException;
use Evenement\EventEmitter;
use InvalidArgumentException;
use React\EventLoop\LoopInterface;
use RuntimeException;
use function function_exists;
use function get_resource_type;
use function inotify_queue_len;
use function inotify_read;
use function is_resource;
use function restore_error_handler;
use function set_error_handler;
use function stream_get_meta_data;
use function stream_set_blocking;
use function stream_set_read_buffer;
use function strpos;

final class InotifyStream extends EventEmitter
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var bool
     */
    private $closed = false;

    /**
     * @var bool
     */
    private $listening = false;

    /**
     * @param resource $stream
     * @psalm-suppress RedundantConditionGivenDocblockType
     * @psalm-suppress DocblockTypeContradiction
     */
    public function __construct($stream, LoopInterface $loop)
    {
        if (
            !is_resource($stream) ||
            get_resource_type($stream) !== 'stream'
        ) {
            throw new InvalidArgumentException(
                'First parameter must be a valid stream resource'
            );
        }

        // ensure resource is opened for reading (mode must contain "r" or "+")
        $meta = stream_get_meta_data($stream);

        if (
            isset($meta['mode']) &&
            $meta['mode'] !== '' &&
            strpos($meta['mode'], 'r') === strpos($meta['mode'], '+')
        ) {
            throw new InvalidArgumentException(
                'Given stream resource is not opened in read mode'
            );
        }

        // this class relies on non-blocking I/O in order to not interrupt
        // the event loop e.g. pipes on Windows do not support this:
        // https://bugs.php.net/bug.php?id=47918
        if (stream_set_blocking($stream, false) !== true) {
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
            stream_set_read_buffer($stream, 0);
        }

        $this->stream = $stream;
        $this->loop   = $loop;

        $this->resume();
    }

    public function isReadable(): bool
    {
        return !$this->closed;
    }

    public function pause(): void
    {
        if ($this->listening) {
            $this->loop->removeReadStream($this->stream);
            $this->listening = false;
        }
    }

    public function resume(): void
    {
        if (
            !$this->listening &&
            !$this->closed
        ) {
            $this->loop->addReadStream($this->stream, [$this, 'handleData']);
            $this->listening = true;
        }
    }

    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $this->closed = true;

        $this->emit('close');
        $this->pause();
        $this->removeAllListeners();
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

        while (inotify_queue_len($this->stream)) {
            $events[] = inotify_read($this->stream);
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
            $this->close();

            return;
        }

        if (count($events)) {
            $this->emit('event', $events);
        }
    }
}
