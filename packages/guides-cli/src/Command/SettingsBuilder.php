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

use phpDocumentor\Guides\Event\PostProjectNodeCreated;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

use function assert;
use function count;
use function is_dir;
use function method_exists;
use function pathinfo;
use function sprintf;

/** @internal  */
final class SettingsBuilder
{
    private ProjectSettings $settings;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SettingsManager $settingsManager,
        private readonly ClockInterface $clock,
    ) {
    }

    public function overrideWithInput(InputInterface $input): void
    {
        $settings = $this->settingsManager->getProjectSettings();

        if ($settings->isShowProgressBar() && $input->hasOption('progress')) {
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

        $this->settings = $settings;
    }

    public function createProjectNode(): ProjectNode
    {
        $projectNode = new ProjectNode(
            $this->settings->getTitle() === '' ? null : $this->settings->getTitle(),
            $this->settings->getVersion() === '' ? null : $this->settings->getVersion(),
            $this->settings->getRelease() === '' ? null : $this->settings->getRelease(),
            $this->settings->getCopyright() === '' ? null : $this->settings->getCopyright(),
            $this->clock->now(),
        );

        $event = new PostProjectNodeCreated($projectNode, $this->settings);
        $event = $this->eventDispatcher->dispatch($event);
        assert($event instanceof PostProjectNodeCreated);
        $projectNode = $event->getProjectNode();
        $this->settings = $event->getSettings();

        return $projectNode;
    }

    public function getSettings(): ProjectSettings
    {
        $inputDir = $this->settings->getInput();
        if (!is_dir($inputDir)) {
            throw new RuntimeException(sprintf('Input directory "%s" was not found! ' . "\n" .
                'Run "vendor/bin/guides -h" for information on how to configure this command.', $inputDir));
        }

        return $this->settings;
    }

    public function configureCommand(Command $command): void
    {
        $command->addArgument(
            'input',
            InputArgument::OPTIONAL,
            'Directory which holds the files to render',
        );
        $command->addOption(
            'output',
            null,
            InputOption::VALUE_REQUIRED,
            'Directory to write rendered files to',
        );

        $command->addOption(
            'input-file',
            null,
            InputOption::VALUE_REQUIRED,
            'If set, only the specified file is parsed, relative to the directory specified in "input"',
        );

        $command->addOption(
            'exclude-path',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Paths to exclude, doc files in these directories will not be parsed',
        );

        $command->addOption(
            'input-format',
            null,
            InputOption::VALUE_REQUIRED,
            'Format of the input can be "RST", or "Markdown"',
        );
        $command->addOption(
            'output-format',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Format of the input can be "html" and/or "interlink"',
        );

        $command->addOption(
            'theme',
            null,
            InputOption::VALUE_REQUIRED,
            'The theme used for rendering',
        );
    }
}
