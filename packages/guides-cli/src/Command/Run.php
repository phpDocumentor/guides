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

use Flyfinder\Finder;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Tactician\CommandBus;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use phpDocumentor\Guides\Cli\Logger\SpyProcessor;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Event\PostCollectFilesForParsingEvent;
use phpDocumentor\Guides\Event\PostParseDocument;
use phpDocumentor\Guides\Event\PostParseProcess;
use phpDocumentor\Guides\Event\PostProjectNodeCreated;
use phpDocumentor\Guides\Event\PostRenderDocument;
use phpDocumentor\Guides\Event\PostRenderProcess;
use phpDocumentor\Guides\Event\PreParseDocument;
use phpDocumentor\Guides\Event\PreRenderDocument;
use phpDocumentor\Guides\Event\PreRenderProcess;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\ParseFileCommand;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;
use Psr\Clock\ClockInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function array_pop;
use function assert;
use function count;
use function implode;
use function is_countable;
use function is_dir;
use function microtime;
use function pathinfo;
use function sprintf;
use function strtoupper;

final class Run extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly Logger $logger,
        private readonly ThemeManager $themeManager,
        private readonly SettingsManager $settingsManager,
        private readonly ClockInterface $clock,
        private readonly EventDispatcher $eventDispatcher,
    ) {
        parent::__construct('run');

        $this->addArgument(
            'input',
            InputArgument::OPTIONAL,
            'Directory which holds the files to render',
        );
        $this->addOption(
            'output',
            null,
            InputOption::VALUE_REQUIRED,
            'Directory to write rendered files to',
        );

        $this->addOption(
            'input-file',
            null,
            InputOption::VALUE_REQUIRED,
            'If set, only the specified file is parsed, relative to the directory specified in "input"',
        );

        $this->addOption(
            'input-format',
            null,
            InputOption::VALUE_REQUIRED,
            'Format of the input can be "RST", or "Markdown"',
        );
        $this->addOption(
            'output-format',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Format of the input can be "html" and/or "interlink"',
        );
        $this->addOption(
            'log-path',
            null,
            InputOption::VALUE_REQUIRED,
            'Write rendering log to this path',
        );
        $this->addOption(
            'fail-on-log',
            null,
            InputOption::VALUE_NONE,
            'If set, returns a non-zero exit code as soon as any warnings/errors occur',
        );

        $this->addOption(
            'fail-on-error',
            null,
            InputOption::VALUE_NONE,
            'If set, returns a non-zero exit code as soon as any errors occur',
        );

        $this->addOption(
            'theme',
            null,
            InputOption::VALUE_REQUIRED,
            'The theme used for rendering',
        );

        $this->addOption(
            'progress',
            null,
            InputOption::VALUE_NEGATABLE,
            'Whether to show a progress bar',
            true,
        );
    }

    public function registerProgressBar(ConsoleOutputInterface $output): void
    {
        $parsingProgressBar = new ProgressBar($output->section());
        $parsingProgressBar->setFormat('Parsing: %current%/%max% [%bar%] %percent:3s%% %message%');
        $parsingStartTime = microtime(true);
        $this->eventDispatcher->addListener(
            PostCollectFilesForParsingEvent::class,
            static function (PostCollectFilesForParsingEvent $event) use ($parsingProgressBar, &$parsingStartTime): void {
                // Each File needs to be first parsed then rendered
                $parsingStartTime = microtime(true);
                $parsingProgressBar->setMaxSteps(count($event->getFiles()));
            },
        );
        $this->eventDispatcher->addListener(
            PreParseDocument::class,
            static function (PreParseDocument $event) use ($parsingProgressBar): void {
                $parsingProgressBar->setMessage('Parsing file: ' . $event->getFileName());
                $parsingProgressBar->display();
            },
        );
        $this->eventDispatcher->addListener(
            PostParseDocument::class,
            static function (PostParseDocument $event) use ($parsingProgressBar): void {
                $parsingProgressBar->advance();
            },
        );
        $this->eventDispatcher->addListener(
            PostParseProcess::class,
            static function (PostParseProcess $event) use ($parsingProgressBar, $parsingStartTime): void {
                $parsingTimeElapsed = microtime(true) - $parsingStartTime;
                $parsingProgressBar->setMessage(sprintf(
                    'Parsed %s files in %.2f seconds',
                    $parsingProgressBar->getMaxSteps(),
                    $parsingTimeElapsed,
                ));
                $parsingProgressBar->finish();
            },
        );
        $that = $this;
        $this->eventDispatcher->addListener(
            PreRenderProcess::class,
            static function (PreRenderProcess $event) use ($that, $output): void {
                $renderingProgressBar = new ProgressBar($output->section(), count($event->getCommand()->getDocumentArray()));
                $renderingProgressBar->setFormat('Rendering: %current%/%max% [%bar%] %percent:3s%% Output format ' . $event->getCommand()->getOutputFormat() . ': %message%');
                $renderingStartTime = microtime(true);
                $that->eventDispatcher->addListener(
                    PreRenderDocument::class,
                    static function (PreRenderDocument $event) use ($renderingProgressBar): void {
                        $renderingProgressBar->setMessage('Rendering: ' . $event->getCommand()->getFileDestination());
                        $renderingProgressBar->display();
                    },
                );
                $that->eventDispatcher->addListener(
                    PostRenderDocument::class,
                    static function (PostRenderDocument $event) use ($renderingProgressBar): void {
                        $renderingProgressBar->advance();
                    },
                );
                $that->eventDispatcher->addListener(
                    PostRenderProcess::class,
                    static function (PostRenderProcess $event) use ($renderingProgressBar, $renderingStartTime): void {
                        $renderingElapsedTime = microtime(true) - $renderingStartTime;
                        $renderingProgressBar->setMessage(sprintf(
                            'Rendered %s documents in %.2f seconds',
                            $renderingProgressBar->getMaxSteps(),
                            $renderingElapsedTime,
                        ));
                        $renderingProgressBar->finish();
                    },
                );
            },
        );
    }

    private function getSettingsOverriddenWithInput(InputInterface $input): ProjectSettings
    {
        $settings = $this->settingsManager->getProjectSettings();

        if ($settings->isShowProgressBar()) {
            $settings->setShowProgressBar($input->getOption('progress'));
        }

        if ($input->getArgument('input')) {
            $settings->setInput((string) $input->getArgument('input'));
        }

        if ($input->getOption('output')) {
            $settings->setOutput((string) $input->getOption('output'));
        }

        if ($input->getOption('input-file')) {
            $inputFile = (string) $input->getOption('input-file');
            $pathInfo = pathinfo($inputFile);
            $settings->setInputFile($pathInfo['filename']);
            if (!empty($pathInfo['extension'])) {
                $settings->setInputFormat($pathInfo['extension']);
            }
        }

        if ($input->getOption('input-format')) {
            $settings->setInputFormat((string) $input->getOption('input-format'));
        }

        if ($input->getOption('log-path')) {
            $settings->setLogPath((string) $input->getOption('log-path'));
        }

        if ($input->getOption('fail-on-error')) {
            $settings->setFailOnError(LogLevel::ERROR);
        }

        if ($input->getOption('fail-on-log')) {
            $settings->setFailOnError(LogLevel::WARNING);
        }

        if (count($input->getOption('output-format')) > 0) {
            $settings->setOutputFormats($input->getOption('output-format'));
        }

        if ($input->getOption('theme')) {
            $settings->setTheme((string) $input->getOption('theme'));
        }

        return $settings;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $settings = $this->getSettingsOverriddenWithInput($input);
        $inputDir = $settings->getInput();
        if (!is_dir($inputDir)) {
            throw new RuntimeException(sprintf('Input directory "%s" was not found! ' . "\n" .
                'Run "vendor/bin/guides -h" for information on how to configure this command.', $inputDir));
        }

        $projectNode = new ProjectNode(
            $settings->getTitle() === '' ? null : $settings->getTitle(),
            $settings->getVersion() === '' ? null : $settings->getVersion(),
            $settings->getRelease() === '' ? null : $settings->getRelease(),
            $settings->getCopyright() === '' ? null : $settings->getCopyright(),
            $this->clock->now(),
        );

        $event = new PostProjectNodeCreated($projectNode, $settings);
        $event = $this->eventDispatcher->dispatch($event);
        assert($event instanceof PostProjectNodeCreated);
        $projectNode = $event->getProjectNode();
        $settings = $event->getSettings();

        $outputDir = $settings->getOutput();
        $sourceFileSystem = new Filesystem(new Local($settings->getInput()));
        $sourceFileSystem->addPlugin(new Finder());
        $logPath = $settings->getLogPath();
        if ($logPath === 'php://stder') {
            $this->logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::WARNING));
        } else {
            $this->logger->pushHandler(new StreamHandler($logPath . '/warning.log', Logger::WARNING));
            $this->logger->pushHandler(new StreamHandler($logPath . '/error.log', Logger::ERROR));
        }

        if ($settings->isFailOnError()) {
            $spyProcessor = new SpyProcessor($settings->getFailOnError());
            $this->logger->pushProcessor($spyProcessor);
        }

        $documents = [];


        if ($output instanceof ConsoleOutputInterface && $settings->isShowProgressBar()) {
            $this->registerProgressBar($output);
        }

        if ($settings->getInputFile() === '') {
            $documents = $this->commandBus->handle(
                new ParseDirectoryCommand(
                    $sourceFileSystem,
                    '',
                    $settings->getInputFormat(),
                    $projectNode,
                ),
            );
        } else {
            $documents[] = $this->commandBus->handle(
                new ParseFileCommand(
                    $sourceFileSystem,
                    '',
                    $settings->getInputFile(),
                    $settings->getInputFormat(),
                    1,
                    $projectNode,
                    true,
                ),
            );
        }

        $this->themeManager->useTheme($settings->getTheme());

        $documents = $this->commandBus->handle(new CompileDocumentsCommand($documents, new CompilerContext($projectNode)));

        $destinationFileSystem = new Filesystem(new Local($outputDir));

        $outputFormats = $settings->getOutputFormats();

        foreach ($outputFormats as $format) {
            $this->commandBus->handle(
                new RenderCommand(
                    $format,
                    $documents,
                    $sourceFileSystem,
                    $destinationFileSystem,
                    $projectNode,
                ),
            );
        }

        if ($output->isQuiet() === false) {
            $lastFormat = '';

            if ((is_countable($outputFormats) ? count($outputFormats) : 0) > 1) {
                $lastFormat = ((is_countable($outputFormats) ? count($outputFormats) : 0) > 2 ? ',' : '') . ' and ' . strtoupper((string) array_pop($outputFormats));
            }

            $formatsText = strtoupper(implode(', ', $outputFormats)) . $lastFormat;

            $output->writeln(
                'Successfully placed ' . (is_countable($documents) ? count($documents) : 0) . ' rendered ' . $formatsText . ' files into ' . $outputDir,
            );
        }

        if ($settings->isFailOnError() && $spyProcessor->hasBeenCalled()) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
