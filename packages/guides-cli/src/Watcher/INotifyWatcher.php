<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\Watcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use React\EventLoop\LoopInterface;

use function inotify_add_watch;
use function inotify_read;
use function stream_set_blocking;
use function var_dump;

class INotifyWatcher
{
    /** @var resource|false */
    private mixed $inotify = false;

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
        if (($events = inotify_read($this->inotify)) === false) {
            return;
        }

        foreach ($events as $event) {
            if (!isset($this->watchDescriptors[$event['wd']])) {
                continue;
            }

            $path = $this->watchDescriptors[$event['wd']]['path'];
            switch ($event['mask']) {
                case IN_MODIFY:
                    $this->dispatcher->dispatch(new FileModifiedEvent($path));
                    break;
                case IN_CREATE:
                    //$this->dispatcher->dispatch(new FileCreatedEvent($path, $event['name']));
                    break;
                case IN_DELETE:
                    //$this->dispatcher->dispatch(new FileDeletedEvent($path, $event['name']));
                    break;
                default:
                    var_dump('Unhandled event mask: ' . $event['mask']);
            }
        }
    }

    public function addPath(string $path): void
    {
        if ($this->inotify === false) {
            $this->inotify = inotify_init();
            stream_set_blocking($this->inotify, false);

            // wait for any file events by reading from inotify handler asynchronously
            $this->loop->addReadStream($this->inotify, $this);
        }

        $descriptor = inotify_add_watch($this->inotify, $this->inputPath . DIRECTORY_SEPARATOR . $path, IN_MODIFY | IN_CREATE | IN_DELETE);
        $this->watchDescriptors[$descriptor] = ['path' => $path];
    }
}
