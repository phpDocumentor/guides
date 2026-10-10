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
use phpDocumentor\Guides\Logging\DeduplicatingLogger;
use phpDocumentor\Guides\Validation\LoggingViolationReporter;
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
        ->arg('$validationLogger', service('phpdoc.guides.cli.validation_logger'))
        ->public()
        ->tag('phpdoc.guides.cli.command')

        ->set(NativeClock::class)
        ->alias(ClockInterface::class, NativeClock::class)

        ->set(Logger::class)
        ->arg('$name', 'app')

        ->set(DeduplicatingLogger::class)
        ->arg('$logger', service(Logger::class))
        ->arg('$level', '%phpdoc.guides.log_deduplication%')
        ->alias(LoggerInterface::class, DeduplicatingLogger::class)

        // Violations found in the documentation source are logged on their own channel
        ->set('phpdoc.guides.cli.validation_logger', Logger::class)
        ->arg('$name', 'validation')

        ->set(LoggingViolationReporter::class)
        ->arg('$logger', service('phpdoc.guides.cli.validation_logger'))

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

    if (!class_exists(ServerFactory::class)) {
        return;
    }

    $container->services()->defaults()->autowire()->set(ServerFactory::class)
        ->set(Serve::class)
        ->arg('$validationLogger', service('phpdoc.guides.cli.validation_logger'))
        ->public()
        ->tag('phpdoc.guides.cli.command');
};
