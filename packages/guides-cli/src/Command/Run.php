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
use League\Tactician\CommandBus;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use phpDocumentor\Guides\Cli\Internal\RunCommand;
use phpDocumentor\Guides\Cli\Logger\SpyProcessor;
use phpDocumentor\Guides\Settings\SettingsManager;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;
use Psr\Clock\ClockInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function array_pop;
use function count;
use function implode;
use function is_countable;
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
        private SettingsBuilder $settingsBuilder,
    ) {
        parent::__construct('run');

        $this->settingsBuilder ??= new SettingsBuilder($this->eventDispatcher, $this->settingsManager, $this->clock);
        $this->settingsBuilder->configureCommand($this);

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->settingsBuilder->overrideWithInput($input);
        $projectNode = $this->settingsBuilder->createProjectNode();
        $settings = $this->settingsBuilder->getSettings();

        $logPath = $settings->getLogPath();
        if ($logPath === 'php://stder') {
            $this->logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::WARNING));
        } else {
            $this->logger->pushHandler(new StreamHandler($logPath . '/warning.log', Logger::WARNING));
            $this->logger->pushHandler(new StreamHandler($logPath . '/error.log', Logger::ERROR));
        }

        if ($settings->isFailOnError()) {
            $spyProcessor = new SpyProcessor($settings->getFailOnError() ?? LogLevel::WARNING);
            $this->logger->pushProcessor($spyProcessor);
        }

        if ($output instanceof ConsoleOutputInterface && $settings->isShowProgressBar()) {
            $this->progressBarSubscriber->subscribe($output, $this->eventDispatcher);
        }

        $documents = $this->commandBus->handle(
            new RunCommand($settings, $projectNode, $input),
        );

        $outputFormats = $settings->getOutputFormats();
        $outputDir = $settings->getOutput();
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
