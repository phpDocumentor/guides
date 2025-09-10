<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\DevServer\Watcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use React\EventLoop\LoopInterface;
use RuntimeException;

use function inotify_add_watch;
use function inotify_read;
use function stream_set_blocking;
use function var_dump;

use const DIRECTORY_SEPARATOR;

class INotifyWatcher
{
    /** @var resource|null */
    private mixed $inotify = null;

    /** @var array<int, array{path: string}> */
    private array $watchDescriptors;

    public function __construct(
        private LoopInterface $loop,
        private EventDispatcherInterface $dispatcher,
        private string $inputPath,
    ) {
    }

    public function __invoke(): void
    {
        if ($this->inotify === null) {
            throw new RuntimeException('No inotify watcher');
        }

        $events = inotify_read($this->inotify);
        if ($events === false) {
            return;
        }

        foreach ($events as $event) {
            if (!isset($this->watchDescriptors[$event['wd']])) {
                continue;
            }

            $path = $this->watchDescriptors[$event['wd']]['path'];

            // File modified event - triggered by direct modification
            if ($event['mask'] & IN_MODIFY) {
                $this->dispatcher->dispatch(new FileModifiedEvent($path));

                return;
            }

            // File closed after writing - common on macOS/Orbstack
            if ($event['mask'] & IN_CLOSE_WRITE) {
                $this->dispatcher->dispatch(new FileModifiedEvent($path));

                return;
            }

            if ($event['mask'] & IN_CREATE) {
                //$this->dispatcher->dispatch(new FileCreatedEvent($path, $event['name']));
                return;
            }

            if ($event['mask'] & IN_DELETE) {
                //$this->dispatcher->dispatch(new FileDeletedEvent($path, $event['name']));
                return;
            }

            // Log unhandled event types for debugging
            var_dump('Unhandled event mask: ' . $event['mask']);
        }
    }

    public function addPath(string $path): void
    {
        if (isset($this->inotify) === false) {
            $inotify = inotify_init();

            if ($inotify === false) {
                throw new RuntimeException('Failed to initialize inotify');
            }

            $this->inotify = $inotify;

            stream_set_blocking($this->inotify, false);

            // wait for any file events by reading from inotify handler asynchronously
            $this->loop->addReadStream($this->inotify, $this);
        }

        // Add IN_CLOSE_WRITE to the watch mask to support macOS/Orbstack
        $descriptor = inotify_add_watch(
            $this->inotify,
            $this->inputPath . DIRECTORY_SEPARATOR . $path,
            IN_MODIFY | IN_CLOSE_WRITE | IN_CREATE | IN_DELETE,
        );
        $this->watchDescriptors[$descriptor] = ['path' => $path];
    }
}
