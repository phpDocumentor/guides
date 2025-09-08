<?php

declare(strict_types=1);

use Monolog\Logger;
use phpDocumentor\DevServer\ServerFactory;
use phpDocumentor\Guides\Cli\Application;
use phpDocumentor\Guides\Cli\Command\ProgressBarSubscriber;
use phpDocumentor\Guides\Cli\Command\Run;
use phpDocumentor\Guides\Cli\Command\Serve;
use phpDocumentor\Guides\Cli\Command\SettingsBuilder;
use phpDocumentor\Guides\Cli\Command\WorkingDirectorySwitcher;
use phpDocumentor\Guides\Cli\Internal\RunCommand;
use phpDocumentor\Guides\Cli\Internal\RunCommandHandler;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\NativeClock;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
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
        ->call('setDispatcher', [service(EventDispatcherInterface::class)])
        ->public()

        ->set(WorkingDirectorySwitcher::class)
        ->tag('event_listener', ['event' => ConsoleEvents::COMMAND, 'method' => '__invoke'])

        ->set(ProgressBarSubscriber::class)
        ->set(SettingsBuilder::class)
        ->set(RunCommandHandler::class)
        ->tag('phpdoc.guides.command', ['command' => RunCommand::class]);

    if (class_exists(ServerFactory::class)) {
        $container->services()->defaults()->autowire()->set(ServerFactory::class)
            ->set(Serve::class)
            ->public()
            ->tag('phpdoc.guides.cli.command');
    }
};
