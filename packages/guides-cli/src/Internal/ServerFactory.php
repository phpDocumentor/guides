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

use phpDocumentor\FileSystem\FlySystemAdapter;
use Psr\Log\LoggerInterface;
use Ratchet\App;
use Symfony\Component\Routing\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ServerFactory
{
    public function __construct(
        private LoggerInterface $logger,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function createDevServer(
        string $soureDirectory,
        FlySystemAdapter $files,
        string $host,
        string $listen,
        int $port,
    ): Server {
        $httpHandler = new HttpHandler($files);
        $wsServer = new WebSocketHandler($this->logger);

        $app = new App($host, $port, $listen);
        $app->route('/ws', $wsServer, ['*']);

        $app->routes->add('catch-all', new Route(
            '/{url}',
            ['_controller' => $httpHandler],
            ['url' => '.*'],
            methods: ['GET'],
        ));

        return new Server($app, $wsServer, $this->eventDispatcher, $this->logger, $soureDirectory);
    }
}
