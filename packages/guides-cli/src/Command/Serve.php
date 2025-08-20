<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\Command;

use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use League\Tactician\CommandBus;
use phpDocumentor\FileSystem\FlySystemAdapter;
use phpDocumentor\Guides\Cli\Internal\RunCommand;
use phpDocumentor\Guides\Cli\Watcher\FileModifiedEvent;
use phpDocumentor\Guides\Cli\Watcher\INotifyWatcher;
use phpDocumentor\Guides\Event\PostParseDocument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function function_exists;
use function sprintf;
use function trim;

final class Serve extends Command
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private SettingsBuilder $settingsBuilder,
        private CommandBus $commandBus,
    ) {
        parent::__construct('serve');
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

        $http = new HttpServer(static function (ServerRequestInterface $request) use ($output, $files) {
            $output->writeln(
                sprintf(
                    'Received request for %s from %s',
                    $request->getUri(),
                    $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ),
            );

            $requestPath = trim($request->getUri()->getPath(), '/');

            $output->writeln(sprintf(
                'Request path: %s',
                $requestPath,
            ));

            $detector = new ExtensionMimeTypeDetector();
            if ($files->isDirectory($requestPath)) {
                $requestPath .= '/index.html';
            }

            if ($files->has($requestPath)) {
                return Response::html(
                    $files->read($requestPath) ?: '',
                )->withHeader(
                    'Content-Type',
                    $detector->detectMimeTypeFromPath($requestPath) ?? 'text/plain',
                );
            }

            return Response::html(
                "page not found!\n",
            )->withStatus(404);
        });

        $socket = new SocketServer('0.0.0.0:1337', [], $loop);
        $http->listen($socket);

        $output->writeln(sprintf('Server running at http://127.0.0.1:1337'));
        $output->writeln('Press Ctrl+C to stop the server');

        // Handle SIGINT (Ctrl+C) gracefully if PCNTL extension is available
        if (function_exists('pcntl_signal')) {
            // 2 is the signal number for SIGINT (Ctrl+C)
            pcntl_signal(2, static function () use ($loop, $socket, $output): void {
                $output->writeln('Shutting down server...');
                $socket->close();
                $loop->stop();
                $output->writeln('Server stopped');
                exit(0);
            });
        } else {
            $output->writeln('Note: PCNTL extension not available, Ctrl+C handling may not work properly');
        }

        // Create a periodic timer to ensure the loop regularly processes events
        // This helps in processing signals even when there are no other events
        $loop->addPeriodicTimer(0.5, static function (): void {
            // This empty callback just ensures the loop wakes up regularly
            // which improves the responsiveness to signals
        });

        $loop->run();

        return Command::SUCCESS;
    }
}
