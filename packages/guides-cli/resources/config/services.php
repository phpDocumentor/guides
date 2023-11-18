<?php

declare(strict_types=1);

use Monolog\Logger;
use phpDocumentor\Guides\Cli\Application;
use phpDocumentor\Guides\Cli\Command\Run;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\NativeClock;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()->autowire()

        ->set(Run::class)
        ->public()
        ->tag('phpdoc.guides.cli.command')

        ->set(NativeClock::class)
        ->alias(ClockInterface::class, NativeClock::class)

        ->set(Logger::class)
        ->arg('$name', 'app')
        ->alias(LoggerInterface::class, Logger::class)

        ->set(EventDispatcher::class)
        ->alias(EventDispatcherInterface::class, EventDispatcher::class)

        ->set(Application::class)
        ->arg('$commands', tagged_iterator('phpdoc.guides.cli.command'))
        ->public();
};
