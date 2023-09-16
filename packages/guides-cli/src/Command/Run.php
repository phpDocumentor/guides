<?php

declare(strict_types=1);

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
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Intersphinx\InventoryRepository;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Settings\SettingsManager;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_pop;
use function count;
use function getcwd;
use function implode;
use function is_countable;
use function is_dir;
use function realpath;
use function sprintf;
use function str_starts_with;
use function strtoupper;

final class Run extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly Logger $logger,
        private readonly ThemeManager $themeManager,
        private readonly SettingsManager $settingsManager,
        private readonly InventoryRepository $inventoryRepository,
    ) {
        parent::__construct('run');

        $this->addArgument(
            'input',
            InputArgument::OPTIONAL,
            'Directory to read for files',
            'docs',
        );
        $this->addArgument(
            'output',
            InputArgument::OPTIONAL,
            'Directory to read for files',
            'output',
        );

        $this->addOption(
            'input-format',
            null,
            InputOption::VALUE_REQUIRED,
            'Format of the input can be RST, or Markdown',
            'rst',
        );
        $this->addOption(
            'output-format',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Format of the input can be html',
            ['html'],
        );
        $this->addOption(
            'log-path',
            null,
            InputOption::VALUE_REQUIRED,
            'Write log to this path',
            'php://stder',
        );
        $this->addOption(
            'fail-on-log',
            null,
            InputOption::VALUE_NONE,
            'Use a non-zero exit code as soon as any log is written',
        );

        $this->addOption(
            'theme',
            null,
            InputOption::VALUE_REQUIRED,
            'The theme used for rendering.',
        );

        $this->addOption(
            'progress',
            null,
            InputOption::VALUE_NEGATABLE,
            'Whether to show a progress bar',
            true,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputDir = $this->getAbsolutePath((string) ($input->getArgument('input') ?? ''));
        if (!is_dir($inputDir)) {
            throw new RuntimeException(sprintf('Input directory "%s" was not found! ' . "\n" .
                'Run "vendor/bin/guides -h" for information on how to configure this command.', $inputDir));
        }

        $settings = $this->settingsManager->getProjectSettings();

        $projectNode = new ProjectNode(
            $settings->getTitle() === '' ? null : $settings->getTitle(),
            $settings->getVersion() === '' ? null : $settings->getVersion(),
        );
        $this->inventoryRepository->initialize($settings->getInventories());

        $outputDir = $this->getAbsolutePath((string) ($input->getArgument('output') ?? ''));
        $sourceFileSystem = new Filesystem(new Local($input->getArgument('input')));
        $sourceFileSystem->addPlugin(new Finder());
        $logPath = $input->getOption('log-path') ?? 'php://stder';
        if ($logPath === 'php://stder') {
            $this->logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::WARNING));
        } else {
            $this->logger->pushHandler(new StreamHandler($logPath . '/warning.log', Logger::WARNING));
            $this->logger->pushHandler(new StreamHandler($logPath . '/error.log', Logger::ERROR));
        }

        $failOnLog = $input->getOption('fail-on-log') ?? false;

        if ($failOnLog) {
            $spyProcessor = new SpyProcessor();
            $this->logger->pushProcessor($spyProcessor);
        }

        $documents = $this->commandBus->handle(
            new ParseDirectoryCommand(
                $sourceFileSystem,
                '',
                $input->getOption('input-format'),
                $projectNode,
            ),
        );

        $theme = $input->getOption('theme');
        if ($theme) {
            $settings->setTheme($theme);
        }

        $this->themeManager->useTheme($settings->getTheme());

        $documents = $this->commandBus->handle(new CompileDocumentsCommand($documents, new CompilerContext($projectNode)));

        $destinationFileSystem = new Filesystem(new Local($outputDir));

        $outputFormats = $input->getOption('output-format');

        foreach ($outputFormats as $format) {
            $progressBar = null;

            if ($output instanceof ConsoleOutputInterface && $input->getOption('progress')) {
                $progressBar = new ProgressBar($output->section());
            }

            $this->commandBus->handle(
                new RenderCommand(
                    $format,
                    $documents,
                    $progressBar === null ? $documents : $progressBar->iterate($documents),
                    $sourceFileSystem,
                    $destinationFileSystem,
                    $projectNode,
                ),
            );
        }

        $lastFormat = '';

        if ((is_countable($outputFormats) ? count($outputFormats) : 0) > 1) {
            $lastFormat = ((is_countable($outputFormats) ? count($outputFormats) : 0) > 2 ? ',' : '') . ' and ' . strtoupper((string) array_pop($outputFormats));
        }

        $formatsText = strtoupper(implode(', ', $outputFormats)) . $lastFormat;

        $output->writeln(
            'Successfully placed ' . (is_countable($documents) ? count($documents) : 0) . ' rendered ' . $formatsText . ' files into ' . $outputDir,
        );

        if ($failOnLog && $spyProcessor->hasBeenCalled()) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function getAbsolutePath(string $path): string
    {
        $absolutePath = $path;
        if (!str_starts_with($absolutePath, '/')) {
            if (getcwd() === false) {
                throw new RuntimeException('Cannot find current working directory, use absolute paths.');
            }

            $absolutePath = realpath(getcwd() . '/' . $absolutePath);
            if ($absolutePath === false) {
                throw new RuntimeException('Cannot find path "' . $path . '".');
            }
        }

        return $absolutePath;
    }
}
