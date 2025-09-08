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

namespace phpDocumentor\Guides\Cli\Internal;

use Closure;
use phpDocumentor\Guides\Cli\Internal\Watcher\INotifyWatcher;
use Psr\Log\LoggerInterface;
use Ratchet\App;
use React\EventLoop\Loop;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Server
{
    private INotifyWatcher $watcher;

    public function __construct(
        private App $app,
        private WebSocketHandler $webSocketHandler,
        private EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        string $sourceDirectory,
    ) {
        $loop = Loop::get();
        $this->watcher = new InotifyWatcher($loop, $eventDispatcher, $sourceDirectory);
        $loop->addSignal(SIGINT, static function () use ($logger, $loop): void {
            $logger->info('Shutting down server...');
            $loop->stop();
            $logger->info('Server stopped');
            exit(0);
        });
    }

    public function run(): void
    {
        $this->app->run();
    }

    public function notifyClients(): void
    {
        $this->webSocketHandler->sendUpdate();
    }

    public function watch(string $path): void
    {
        $this->watcher->addPath($path);
    }

    public function addListener(string $event, Closure $param): void
    {
        $this->eventDispatcher->addListener($event, $param);
    }
}
