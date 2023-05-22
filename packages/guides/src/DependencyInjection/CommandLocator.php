<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\DependencyInjection;

use League\Tactician\Exception\MissingHandlerException;
use League\Tactician\Handler\Locator\HandlerLocator;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;
use function is_object;
use function sprintf;

class CommandLocator implements HandlerLocator
{
    public function __construct(private readonly ContainerInterface $commands)
    {
    }

    /** {@inheritDoc} */
    public function getHandlerForCommand($commandName): object
    {
        try {
            $command = $this->commands->get($commandName);
            assert(is_object($command));

            return $command;
        } catch (NotFoundExceptionInterface $e) {
            throw new MissingHandlerException(
                sprintf('No handler found for command "%s"', $commandName),
                $e->getCode(),
                $e,
            );
        }
    }
}
