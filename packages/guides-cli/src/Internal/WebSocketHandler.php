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

use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use SplObjectStorage;
use Throwable;

final class WebSocketHandler implements MessageComponentInterface
{
    /** @var SplObjectStorage<ConnectionInterface, null> */
    private SplObjectStorage $clients;

    public function __construct(private LoggerInterface $logger)
    {
        $this->clients = new SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
        $this->logger->info('New WebSocket connection');
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
        $this->logger->info('WebSocket connection has disconnected');
    }

    public function onError(ConnectionInterface $conn, Throwable $e): void
    {
        $conn->close();
    }

    public function onMessage(ConnectionInterface $conn, MessageInterface $msg): void
    {
        //We do nothing with the message, just a ping to keep the connection alive
    }

    public function sendUpdate(): void
    {
        foreach ($this->clients as $client) {
            $client->send('update');
        }
    }
}
