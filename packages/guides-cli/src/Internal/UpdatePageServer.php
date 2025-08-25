<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\Internal;

use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use SplObjectStorage;
use Throwable;

final class UpdatePageServer implements MessageComponentInterface
{
    private $clients;

    public function __construct()
    {
        $this->clients = new SplObjectStorage();
    }

    function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    function onClose(ConnectionInterface $conn): void
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    function onError(ConnectionInterface $conn, Throwable $e): void
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
