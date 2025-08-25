<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\Internal;

use phpDocumentor\FileSystem\FlySystemAdapter;
use Psr\Log\LoggerInterface;
use Ratchet\App;
use React\EventLoop\LoopInterface;
use Symfony\Component\Routing\Route;

final class ServerFactory
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function createWebserver(FlySystemAdapter $files, LoopInterface|null $loop, string $host, string $listen, int $port): Server
    {
        $httpHandler = new HttpHandler($this->logger, $files);
        $wsServer = new WebSocketHandler();
        $host = 'localhost';


        // Setup the Ratchet App with routes
        $app = new App($host, $port, $listen, $loop);

        // Add WebSocket route at /ws
        $app->route('/ws', $wsServer, ['*']);

        // Add HTTP server for all other routes - use a different pattern syntax
        $app->routes->add('catch-all', new Route(
            '/{url}',
            ['_controller' => $httpHandler],
            ['url' => '.*'],
            methods: ['GET'],
        ));

        return new Server($app, $wsServer);
    }
}
