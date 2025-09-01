<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\Cli\Command;

use League\Tactician\CommandBus;
use Monolog\Handler\ErrorLogHandler;
use phpDocumentor\FileSystem\FlySystemAdapter;
use phpDocumentor\Guides\Cli\Internal\RunCommand;
use phpDocumentor\Guides\Cli\Internal\ServerFactory;
use phpDocumentor\Guides\Cli\Internal\Watcher\FileModifiedEvent;
use phpDocumentor\Guides\Cli\Internal\Watcher\INotifyWatcher;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Event\PostParseDocument;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseFileCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\DocumentListIterator;
use phpDocumentor\Guides\Renderer\DocumentTreeIterator;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\Loop;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

final class Serve extends Command
{
    public function __construct(
        private LoggerInterface $logger,
        private EventDispatcherInterface $dispatcher,
        private SettingsBuilder $settingsBuilder,
        private CommandBus $commandBus,
        private ServerFactory $serverFactory,
    ) {
        parent::__construct('serve');
    }

    protected function configure(): void
    {
        $this->settingsBuilder->configureCommand($this);
        $this->addOption('host', null, InputOption::VALUE_REQUIRED, 'Hostname to serve on', 'localhost');
        $this->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'Port to run the server on', 1337);
        $this->addOption('listen', 'l', InputOption::VALUE_REQUIRED, 'Address to listen on', '0.0.0.0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM));
        // Enable tick processing for signal handling
        declare(ticks=1);

        $loop = Loop::get();
        $loop->addSignal(SIGINT, static function () use ($loop, $output): void {
            $output->writeln('Shutting down server...');
            $loop->stop();
            $output->writeln('Server stopped');
            exit(0);
        });


        $dir = $input->getOption('output');
        if ($dir === null) {
            $output->writeln('<error>Please specify an output directory using --output option</error>');

            return Command::FAILURE;
        }

        $files = FlySystemAdapter::createForPath($dir);
        $app =  $this->serverFactory->createWebserver(
            $files,
            $loop,
            $input->getOption('host'),
            $input->getOption('listen'),
            (int) $input->getOption('port'),
        );

        $watcher = new InotifyWatcher($loop, $this->dispatcher, $input->getArgument('input'));

        $this->dispatcher->addListener(
            PostParseDocument::class,
            static function (PostParseDocument $event) use ($watcher): void {
                $watcher->addPath($event->getOriginalFileName());
            },
        );

        $this->settingsBuilder->overrideWithInput($input);

        $settings = $this->settingsBuilder->getSettings();
        $projectNode = $this->settingsBuilder->createProjectNode();
        $sourceFileSystem = FlySystemAdapter::createForPath($settings->getInput());

        $documents = $this->commandBus->handle(
            new RunCommand(
                $settings,
                $projectNode,
                $input,
            ),
        );

        $this->dispatcher->addListener(
            FileModifiedEvent::class,
            function (FileModifiedEvent $event) use ($sourceFileSystem, $projectNode, $settings, $app, $output): void {
                $output->writeln(
                    sprintf(
                        'File modified: %s, rerendering...',
                        $event->path,
                    ),
                );
                $file = substr($event->path, 0, -4);

                $documents[$file] = $this->commandBus->handle(
                    new ParseFileCommand(
                        $sourceFileSystem,
                        '',
                        $file,
                        $settings->getInputFormat(),
                        1,
                        $projectNode,
                        true,
                    ),
                );

                $documents = $this->commandBus->handle(new CompileDocumentsCommand($documents, new CompilerContext($projectNode)));
                $destinationFileSystem = FlySystemAdapter::createForPath($settings->getOutput());

                $outputFormats = $settings->getOutputFormats();

                $documentIterator = new DocumentListIterator(
                    new DocumentTreeIterator(
                        [$projectNode->getRootDocumentEntry()],
                        $documents,
                    ),
                    $documents,
                );

                //foreach ($outputFormats as $format) {

                    $renderContext = RenderContext::forProject(
                        $projectNode,
                        $documents,
                        $sourceFileSystem,
                        $destinationFileSystem,
                        '/',
                        'html',
                    )->withIterator($documentIterator);

                    $this->commandBus->handle(
                        new RenderDocumentCommand(
                            $documents[$file],
                            $renderContext->withDocument($documents[$file]),
                        ),
                    );
                //}

                $output->writeln('Rerendering completed.');
                $app->notifyClients();
            },
        );


        $output->writeln(sprintf('Server running at http://localhost:1337'));
        $output->writeln('WebSocket server running at ws://localhost:1337/ws');
        $output->writeln('Press Ctrl+C to stop the server');

        $app->run();

        return Command::SUCCESS;
    }
}
