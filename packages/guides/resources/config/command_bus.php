<?php

declare(strict_types=1);

use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\Plugins\LockingMiddleware;
use phpDocumentor\Guides\DependencyInjection\CommandLocator;
use phpDocumentor\Guides\FileCollector;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\CompileDocumentsHandler;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryHandler;
use phpDocumentor\Guides\Handlers\ParseFileCommand;
use phpDocumentor\Guides\Handlers\ParseFileHandler;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentHandler;
use phpDocumentor\Guides\Handlers\RenderHandler;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()->autowire()

        ->set(ParseFileHandler::class)
        ->tag('phpdoc.guides.command', ['command' => ParseFileCommand::class])

        ->set(ParseDirectoryHandler::class)
        ->args([inline_service(FileCollector::class)->autowire()])
        ->tag('phpdoc.guides.command', ['command' => ParseDirectoryCommand::class])

        ->set(RenderHandler::class)
        ->tag('phpdoc.guides.command', ['command' => RenderCommand::class])

        ->set(CompileDocumentsHandler::class)
        ->tag('phpdoc.guides.command', ['command' => CompileDocumentsCommand::class])

        ->set(RenderDocumentHandler::class)
        ->tag('phpdoc.guides.command', ['command' => RenderDocumentCommand::class])
        ->arg('$renderer', service('phpdoc.guides.output_node_renderer'))
        ->arg('$eventDispatcher', service(EventDispatcherInterface::class))

        ->set(CommandBus::class)
        ->args([
            [
                inline_service(CommandHandlerMiddleware::class)
                ->args([
                    inline_service(ClassNameExtractor::class),
                    inline_service(CommandLocator::class)->args(
                        [tagged_locator('phpdoc.guides.command', 'command')],
                    ),
                    inline_service(HandleInflector::class),
                ]),
                inline_service(LockingMiddleware::class),
            ],
        ]);
};
