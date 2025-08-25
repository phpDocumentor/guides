<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\Command;

use League\Tactician\CommandBus;
use phpDocumentor\FileSystem\FlySystemAdapter;
use phpDocumentor\Guides\Cli\Internal\HttpHandler;
use phpDocumentor\Guides\Cli\Internal\RunCommand;
use phpDocumentor\Guides\Cli\Internal\UpdatePageServer;
use phpDocumentor\Guides\Cli\Watcher\FileModifiedEvent;
use phpDocumentor\Guides\Cli\Watcher\INotifyWatcher;
use phpDocumentor\Guides\Event\PostParseDocument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Ratchet\App;
use React\EventLoop\Loop;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Routing\Route;
use function function_exists;
use function sprintf;

final class Serve extends Command
{
    private UpdatePageServer $wsServer;

    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private SettingsBuilder $settingsBuilder,
        private CommandBus $commandBus,
    ) {
        parent::__construct('serve');
        $this->wsServer = new UpdatePageServer();
    }

    protected function configure(): void
    {
        $this->settingsBuilder->configureCommand($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Enable tick processing for signal handling
        declare(ticks=1);

        $loop = Loop::get();

        $watcher = new InotifyWatcher($loop, $this->dispatcher, $input->getArgument('input'));

        $this->dispatcher->addListener(
            PostParseDocument::class,
            static function (PostParseDocument $event) use ($watcher): void {
                $watcher->addPath($event->getOriginalFileName());
            },
        );

        $this->dispatcher->addListener(
            FileModifiedEvent::class,
            function (FileModifiedEvent $event) use ($input, $output): void {
                $output->writeln(
                    sprintf(
                        'File modified: %s, rerendering...',
                        $event->path,
                    ),
                );
                $this->commandBus->handle(
                    new RunCommand(
                        $this->settingsBuilder->getSettings(),
                        $this->settingsBuilder->createProjectNode(),
                        $input,
                    ),
                );
                $output->writeln('Rerendering completed.');

                // Notify connected clients that they should reload
                $this->wsServer->sendUpdate();
            },
        );

        $this->settingsBuilder->overrideWithInput($input);

        $this->commandBus->handle(
            new RunCommand(
                $this->settingsBuilder->getSettings(),
                $this->settingsBuilder->createProjectNode(),
                $input,
            ),
        );

        $dir = $input->getOption('output');
        if ($dir === null) {
            $output->writeln('<error>Please specify an output directory using --output option</error>');

            return Command::FAILURE;
        }

        $files = FlySystemAdapter::createForPath($dir);

        // Create HTTP handler for serving files
        $httpHandler = new HttpHandler($output, $files);

        // Setup the Ratchet App with routes
        $app = new App('localhost', 1337, '0.0.0.0', $loop);

        // Add WebSocket route at /ws
        $app->route('/ws', $this->wsServer, ['*']);

        // Add root path first
        $app->route('/', $httpHandler, ['*']);

        // Add HTTP server for all other routes - use a different pattern syntax
        $app->routes->add('catch-all', new Route(
            '/{url}',
            ['_controller' => $httpHandler],
            ['url' => '.+'],
            [],
            'localhost',
            [],
            ['GET'],
        ));

        $output->writeln(sprintf('Server running at http://localhost:1337'));
        $output->writeln('WebSocket server running at ws://localhost:1337/ws');
        $output->writeln('Press Ctrl+C to stop the server');

        // Handle SIGINT (Ctrl+C) gracefully if PCNTL extension is available
        if (function_exists('pcntl_signal')) {
            // 2 is the signal number for SIGINT (Ctrl+C)
            pcntl_signal(2, static function () use ($loop, $output): void {
                $output->writeln('Shutting down server...');
                $loop->stop();
                $output->writeln('Server stopped');
                exit(0);
            });
        } else {
            $output->writeln('Note: PCNTL extension not available, Ctrl+C handling may not work properly');
        }

        $app->run();

        return Command::SUCCESS;
    }
}
