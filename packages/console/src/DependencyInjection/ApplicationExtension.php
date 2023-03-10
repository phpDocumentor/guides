<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Console\DependencyInjection;

use League\Tactician\CommandBus;
use League\Tactician\Setup\QuickStart;
use phpDocumentor\Guides\FileCollector;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryHandler;
use phpDocumentor\Guides\Handlers\ParseFileCommand;
use phpDocumentor\Guides\Handlers\ParseFileHandler;
use phpDocumentor\Guides\Parser;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class ApplicationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $commandBusDefinition = new Definition(CommandBus::class);
        $commandBusDefinition->setLazy(true);
        $container->setDefinition('phpdoc.guides.commandbus', $commandBusDefinition);
        $container->setAlias(CommandBus::class, 'phpdoc.guides.commandbus');

        $handler = new Definition(ParseDirectoryHandler::class,
            [
                '$scanner' => new Reference(FileCollector::class),
                '$commandBus' => new Reference('phpdoc.guides.commandbus')
            ]
        );

        $container->setDefinition('phpdoc.guides.handler.parsedirectory', $handler);
        $container->setDefinition('phpdoc.guides.handler.parsefile',
            new Definition(
                ParseFileHandler::class,
                [
                    '$logger' => new Reference(LoggerInterface::class),
                    '$parser' => new Reference(Parser::class),
                    '$eventDispatcher' => new Reference(EventDispatcherInterface::class),
                ]
            )
        );

        $commandBusDefinition->setFactory([QuickStart::class, 'create']);
        $commandBusDefinition->setArgument(
            '$commandToHandlerMap',
            [
                ParseFileCommand::class => new Reference('phpdoc.guides.handler.parsefile'),
                ParseDirectoryCommand::class => new Reference('phpdoc.guides.handler.parsedirectory')
            ]
        );




    }

    public function getAlias(): string
    {
        return 'application';
    }
}
