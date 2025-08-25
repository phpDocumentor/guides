<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\Internal;

use Ratchet\App;

class Server
{
    public function __construct(
        private App $app,
        private WebSocketHandler $webSocketHandler,
    ) {
    }

    public function run(): void
    {
        $this->app->run();
    }

    public function notifyClients(): void
    {
        $this->webSocketHandler->sendUpdate();
    }
}
