<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Console\DependencyInjection;

use League\Tactician\Exception\MissingHandlerException;
use League\Tactician\Handler\Locator\HandlerLocator;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function sprintf;

class CommandLocator implements HandlerLocator
{
    private ContainerInterface $commands;

    public function __construct(ContainerInterface $commands)
    {
        $this->commands = $commands;
    }

    /** {@inheritDoc} */
    public function getHandlerForCommand($commandName): object
    {
        try {
            return $this->commands->get($commandName);
        } catch (NotFoundExceptionInterface $e) {
            throw new MissingHandlerException(
                sprintf('No handler found for command "%s"', $commandName),
                $e->getCode(),
                $e
            );
        }
    }
}
