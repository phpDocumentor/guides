<?php

declare(strict_types=1);

use Monolog\Logger;
use phpDocumentor\Guides\ThemeBootstrap\Application;
use phpDocumentor\Guides\ThemeBootstrap\Command\RunBootstrap;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Loader\FilesystemLoader;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()->autowire()

        ->set(RunBootstrap::class)
        ->public()
        ->tag('phpdoc.guides.cli.command')

        ->set(Logger::class)
        ->arg('$name', 'app')
        ->alias(LoggerInterface::class, Logger::class)

        ->set(EventDispatcher::class)
        ->alias(EventDispatcherInterface::class, EventDispatcher::class)

        ->set(FilesystemLoader::class)
        ->arg(
            '$paths',
            [
                __DIR__ . '/../template/',
                __DIR__ . '/../../../guides/resources/template/html/guides',
            ],
        )

        ->set(Application::class)
        ->arg('$commands', tagged_iterator('phpdoc.guides.cli.command'))
        ->public();
};
