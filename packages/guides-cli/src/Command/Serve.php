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
use Monolog\Logger;
use phpDocumentor\DevServer\ServerFactory;
use phpDocumentor\DevServer\Watcher\FileModifiedEvent;
use phpDocumentor\FileSystem\FlySystemAdapter;
use phpDocumentor\Guides\Cli\Internal\RunCommand;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Event\PostParseDocument;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseFileCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\DocumentListIterator;
use phpDocumentor\Guides\Renderer\DocumentTreeIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function assert;
use function is_int;
use function is_string;
use function sprintf;
use function substr;

final class Serve extends Command
{
    public function __construct(
        private readonly Logger $logger,
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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM));

        $dir = $input->getOption('output');
        if (!is_string($dir)) {
            $output->writeln('<error>Please specify an output directory using --output option</error>');

            return Command::FAILURE;
        }

        $inputDir = $input->getArgument('input');
        if (!is_string($inputDir)) {
            $output->writeln('<error>Please specify an input directory using the input argument</error>');

            return Command::FAILURE;
        }

        $host = $input->getOption('host');
        if (!is_string($host)) {
            $output->writeln('<error>Please specify a valid host using --host option</error>');

            return Command::FAILURE;
        }

        $port = $input->getOption('port');
        if (!is_int($port)) {
            $output->writeln('<error>Please specify a valid port using --port option</error>');

            return Command::FAILURE;
        }

        $this->settingsBuilder->overrideWithInput($input);
        $settings = $this->settingsBuilder->getSettings();

        $files = FlySystemAdapter::createForPath($dir);
        $app =  $this->serverFactory->createDevServer(
            $inputDir,
            $files,
            $host,
            '0.0.0.0',
            $port,
            $settings->getIndexName(),
        );

        $app->addListener(
            PostParseDocument::class,
            static function (PostParseDocument $event) use ($app): void {
                $app->watch($event->getOriginalFileName());
            },
        );

        $projectNode = $this->settingsBuilder->createProjectNode();
        $sourceFileSystem = FlySystemAdapter::createForPath($settings->getInput());

        /** @var array<string, DocumentNode> $documents */
        $documents = $this->commandBus->handle(
            new RunCommand(
                $settings,
                $projectNode,
                $input,
            ),
        );

        $app->addListener(
            FileModifiedEvent::class,
            function (FileModifiedEvent $event) use ($documents, $sourceFileSystem, $projectNode, $settings, $app, $output): void {
                $output->writeln(
                    sprintf(
                        'File modified: %s, rerendering...',
                        $event->path,
                    ),
                );
                $file = substr($event->path, 0, -4);

                $document = $this->commandBus->handle(
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
                assert($document instanceof DocumentNode);

                $documents[$file] = $document;

                /** @var array<string, DocumentNode> $documents */
                $documents = $this->commandBus->handle(new CompileDocumentsCommand($documents, new CompilerContext($projectNode)));
                $destinationFileSystem = FlySystemAdapter::createForPath($settings->getOutput());

                $documentIterator = new DocumentListIterator(
                    new DocumentTreeIterator(
                        [$projectNode->getRootDocumentEntry()],
                        $documents,
                    ),
                    $documents,
                );

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

                $output->writeln('Rerendering completed.');
                $app->notifyClients();
            },
        );

        $output->writeln(
            sprintf(
                'Server running at http://%s:%d',
                $host,
                $port,
            ),
        );
        $output->writeln('Press Ctrl+C to stop the server');

        $app->run();

        return Command::SUCCESS;
    }
}
