<?php

declare(strict_types=1);

use Monolog\Logger;
use phpDocumentor\Guides\Console\Application;
use phpDocumentor\Guides\Console\Command\Run;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()->autowire()

        ->set(Run::class)
        ->tag('phpdoc.guides.cli.command')

        ->set(Logger::class)
        ->arg('$name', 'app')
        ->alias(LoggerInterface::class, Logger::class)

        ->set(EventDispatcher::class)
        ->alias(EventDispatcherInterface::class, EventDispatcher::class)

        ->set(Application::class)
        ->arg('$commands', tagged_iterator('phpdoc.guides.cli.command'))
        ->public();
};
