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

use Doctrine\Deprecations\Deprecation;
use Flyfinder\Path;
use Flyfinder\Specification\InPath;
use Flyfinder\Specification\NotSpecification;
use Flyfinder\Specification\OrSpecification;
use Flyfinder\Specification\SpecificationInterface;
use League\Tactician\CommandBus;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use phpDocumentor\FileSystem\Finder\Exclude;
use phpDocumentor\FileSystem\FlySystemAdapter;
use phpDocumentor\Guides\Cli\Logger\SpyProcessor;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Event\PostProjectNodeCreated;
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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function array_map;
use function array_pop;
use function array_reduce;
use function array_shift;
use function assert;
use function count;
use function implode;
use function is_countable;
use function is_dir;
use function method_exists;
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
        private readonly ProgressBarSubscriber $progressBarSubscriber,
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
            'exclude-path',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Paths to exclude, doc files in these directories will not be parsed',
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

    /** @deprecated this method will be removed in v2 */
    public function registerProgressBar(ConsoleOutputInterface $output): void
    {
        Deprecation::trigger(
            'phpdocumentor/guides-cli',
            'https://github.com/phpDocumentor/guides/issues/1210',
            'Progressbar will be registered via settings',
        );
        $this->progressBarSubscriber->subscribe($output, $this->eventDispatcher);
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

        if (method_exists($settings, 'setExcludes')) {
            /** @var list<string> $excludePaths */
            $excludePaths = (array) $input->getOption('exclude-path');
            if ($excludePaths !== []) {
                $settings->setExcludes(
                    $settings->getExcludes()->withPaths($excludePaths),
                );
            }
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
        $sourceFileSystem = FlySystemAdapter::createForPath($settings->getInput());

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
            $this->progressBarSubscriber->subscribe($output, $this->eventDispatcher);
        }

        if ($settings->getInputFile() === '') {
            $documents = $this->commandBus->handle(
                new ParseDirectoryCommand(
                    $sourceFileSystem,
                    '',
                    $settings->getInputFormat(),
                    $projectNode,
                    $this->getExclude($settings, $input),
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

        $destinationFileSystem = FlySystemAdapter::createForPath($outputDir);

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

    private function getExclude(ProjectSettings $settings, InputInterface|null $input = null): Exclude|SpecificationInterface|null
    {
        if (method_exists($settings, 'getExcludes')) {
            return $settings->getExcludes();
        }

        if ($input === null) {
            return null;
        }

        if ($input->getOption('exclude-path')) {
            /** @var string[] $excludedPaths */
            $excludedPaths = (array) $input->getOption('exclude-path');
            $excludedSpecifications = array_map(static fn (string $path) => new NotSpecification(new InPath(new Path($path))), $excludedPaths);
            $excludedSpecification = array_shift($excludedSpecifications);
            assert($excludedSpecification !== null);

            return array_reduce(
                $excludedSpecifications,
                static fn (SpecificationInterface $carry, SpecificationInterface $spec) => new OrSpecification($carry, $spec),
                $excludedSpecification,
            );
        }

        return null;
    }
}
