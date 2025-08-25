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
