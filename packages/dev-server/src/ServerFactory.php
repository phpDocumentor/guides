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

namespace phpDocumentor\DevServer;

use phpDocumentor\DevServer\Internal\HttpHandler;
use phpDocumentor\DevServer\Internal\WebSocketHandler;
use phpDocumentor\FileSystem\FlySystemAdapter;
use Psr\Log\LoggerInterface;
use Ratchet\App;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Route;

final class ServerFactory
{
    public function __construct(
        private LoggerInterface $logger,
        private readonly EventDispatcher $eventDispatcher,
    ) {
    }

    public function createDevServer(
        string $soureDirectory,
        FlySystemAdapter $files,
        string $host,
        string $listen,
        int $port,
        string $indexFile = 'index.html',
    ): Server {
        $httpHandler = new HttpHandler($files, $indexFile);
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
