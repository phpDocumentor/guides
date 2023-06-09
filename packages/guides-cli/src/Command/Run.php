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
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function array_pop;
use function count;
use function getcwd;
use function implode;
use function is_countable;
use function is_dir;
use function is_file;
use function sprintf;
use function str_starts_with;
use function strtoupper;

final class Run extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly Metas $metas,
        private readonly Logger $logger,
        private readonly ThemeManager $themeManager,
        private readonly SettingsManager $settingsManager,
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
            InputOption::VALUE_OPTIONAL,
            'Format of the input can be RST, or Markdown',
            'rst',
        );
        $this->addOption(
            'output-format',
            null,
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Format of the input can be html',
            ['html'],
        );
        $this->addOption(
            'log-path',
            null,
            InputOption::VALUE_OPTIONAL,
            'Write log to this path',
            'php://stder',
        );

        $this->addOption(
            'theme',
            null,
            InputOption::VALUE_OPTIONAL,
            'The theme used for rendering.',
            'default',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputDir = $this->getAbsolutePath((string) ($input->getArgument('input') ?? ''));
        if (!is_dir($inputDir)) {
            throw new RuntimeException(sprintf('Input directory "%s" was not found! ' . "\n" .
                'Run "vendor/bin/guides -h" for information on how to configure this command.', $inputDir));
        }

        if (is_file($inputDir . '/settings.php')) {
            $settings = require $inputDir . '/settings.php';
            if (!$settings instanceof ProjectSettings) {
                throw new RuntimeException('settings.php must return an instance of ' . ProjectSettings::class);
            }

            $this->settingsManager->setProjectSettings($settings);
            $projectNode = new ProjectNode(
                $settings->getTitle(),
                $settings->getVersion(),
            );
        } else {
            $this->settingsManager->setProjectSettings(new ProjectSettings());
            $projectNode = new ProjectNode();
        }

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

        $documents = $this->commandBus->handle(
            new ParseDirectoryCommand(
                $sourceFileSystem,
                '',
                $input->getOption('input-format'),
                $projectNode,
            ),
        );

        if ($input->hasOption('theme')) {
            $this->themeManager->useTheme($input->getOption('theme') ?? 'default');
        }

        $documents = $this->commandBus->handle(new CompileDocumentsCommand($documents, new CompilerContext($projectNode)));

        $destinationFileSystem = new Filesystem(new Local($outputDir));

        $outputFormats = $input->getOption('output-format');

        foreach ($outputFormats as $format) {
            $this->commandBus->handle(
                new RenderCommand(
                    $format,
                    $documents,
                    $this->metas,
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

        return 0;
    }

    private function getAbsolutePath(string $path): string
    {
        if (!str_starts_with($path, '/')) {
            if (getcwd() === false) {
                throw new RuntimeException('Cannot find current working directory, use absolute paths.');
            }

            $path = getcwd() . '/' . $path;
        }

        return $path;
    }
}
