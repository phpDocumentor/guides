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

namespace phpDocumentor\Guides\Cli\Tests\Unit\Command;

use DateTimeImmutable;
use phpDocumentor\Guides\Cli\Command\SettingsBuilder;
use phpDocumentor\Guides\Event\PostProjectNodeCreated;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

use function assert;
use function getcwd;

final class SettingsBuilderTest extends TestCase
{
    private EventDispatcherInterface&MockObject $eventDispatcher;
    private SettingsManager&MockObject $settingsManager;
    private ClockInterface&MockObject $clock;
    private ProjectSettings $projectSettings;
    private SettingsBuilder $settingsBuilder;
    private string $currentDir;
    private DateTimeImmutable $now;
    private Command $dummyCommand;
    private InputDefinition $inputDefinition;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->settingsManager = $this->createMock(SettingsManager::class);

        $this->now = new DateTimeImmutable('2025-09-12');
        $this->clock = $this->createMock(ClockInterface::class);
        $this->clock->method('now')->willReturn($this->now);

        // Create a real ProjectSettings instance instead of a mock
        $this->projectSettings = new ProjectSettings();
        $this->settingsManager->method('getProjectSettings')->willReturn($this->projectSettings);

        $this->settingsBuilder = new SettingsBuilder(
            $this->eventDispatcher,
            $this->settingsManager,
            $this->clock,
        );

        // Get the current directory to use as the input directory
        $currentDir = getcwd();
        assert($currentDir !== false);
        // Set input to current directory to avoid is_dir() issues
        $this->projectSettings->setInput($currentDir);
        $this->currentDir = $currentDir;

        // Create a dummy command and configure it
        $this->dummyCommand = new Command('dummy');
        $this->settingsBuilder->configureCommand($this->dummyCommand);
        $this->inputDefinition = $this->dummyCommand->getDefinition();
        $this->inputDefinition->addOption(new InputOption('log-path'));
    }

    public function testConfigureCommandAddsInputArgument(): void
    {
        self::assertTrue($this->inputDefinition->hasArgument('input'));
        $argument = $this->inputDefinition->getArgument('input');
        self::assertFalse($argument->isRequired());
        self::assertSame('Directory which holds the files to render', $argument->getDescription());
    }

    public function testConfigureCommandAddsOutputOption(): void
    {
        self::assertTrue($this->inputDefinition->hasOption('output'));
        $option = $this->inputDefinition->getOption('output');
        self::assertTrue($option->isValueRequired());
        self::assertSame('Directory to write rendered files to', $option->getDescription());
    }

    public function testConfigureCommandAddsInputFileOption(): void
    {
        self::assertTrue($this->inputDefinition->hasOption('input-file'));
        $option = $this->inputDefinition->getOption('input-file');
        self::assertTrue($option->isValueRequired());
        self::assertSame('If set, only the specified file is parsed, relative to the directory specified in "input"', $option->getDescription());
    }

    public function testConfigureCommandAddsExcludePathOption(): void
    {
        self::assertTrue($this->inputDefinition->hasOption('exclude-path'));
        $option = $this->inputDefinition->getOption('exclude-path');
        self::assertTrue($option->isValueRequired());
        self::assertTrue($option->isArray());
        self::assertSame('Paths to exclude, doc files in these directories will not be parsed', $option->getDescription());
    }

    public function testConfigureCommandAddsInputFormatOption(): void
    {
        self::assertTrue($this->inputDefinition->hasOption('input-format'));
        $option = $this->inputDefinition->getOption('input-format');
        self::assertTrue($option->isValueRequired());
        self::assertSame('Format of the input can be "RST", or "Markdown"', $option->getDescription());
    }

    public function testConfigureCommandAddsOutputFormatOption(): void
    {
        self::assertTrue($this->inputDefinition->hasOption('output-format'));
        $option = $this->inputDefinition->getOption('output-format');
        self::assertTrue($option->isValueRequired());
        self::assertTrue($option->isArray());
        self::assertSame('Format of the input can be "html" and/or "interlink"', $option->getDescription());
    }

    public function testConfigureCommandAddsThemeOption(): void
    {
        self::assertTrue($this->inputDefinition->hasOption('theme'));
        $option = $this->inputDefinition->getOption('theme');
        self::assertTrue($option->isValueRequired());
        self::assertSame('The theme used for rendering', $option->getDescription());
    }

    public function testCreateProjectNodeCreatesNodeWithCorrectValues(): void
    {
        // Arrange
        $this->projectSettings->setTitle('Test Title');
        $this->projectSettings->setVersion('1.0.0');
        $this->projectSettings->setRelease('Stable');
        $this->projectSettings->setCopyright('2025 phpDocumentor');

        // Setup the event dispatcher to return the event unmodified
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function ($event) {
                return $event;
            });

        // Act
        $this->settingsBuilder->overrideWithInput(new ArrayInput([], $this->inputDefinition));
        $projectNode = $this->settingsBuilder->createProjectNode();

        // Assert
        self::assertInstanceOf(ProjectNode::class, $projectNode);
        self::assertSame('Test Title', $projectNode->getTitle());
        self::assertSame('1.0.0', $projectNode->getVersion());
        self::assertSame('Stable', $projectNode->getRelease());
        self::assertSame('2025 phpDocumentor', $projectNode->getCopyright());
    }

    public function testCreateProjectNodeHandlesEmptyValues(): void
    {
        // Arrange - all values are empty by default
        // Setup the event dispatcher to return the event unmodified
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function ($event) {
                return $event;
            });

        // Act
        $this->settingsBuilder->overrideWithInput(new ArrayInput([], $this->inputDefinition));
        $projectNode = $this->settingsBuilder->createProjectNode();

        // Assert
        self::assertInstanceOf(ProjectNode::class, $projectNode);
        self::assertNull($projectNode->getTitle());
        self::assertNull($projectNode->getVersion());
        self::assertNull($projectNode->getRelease());
        self::assertNull($projectNode->getCopyright());
    }

    public function testCreateProjectNodeDispatchesEvent(): void
    {
        // Arrange
        $this->projectSettings->setTitle('Test Title');

        // Setup the event dispatcher to verify event dispatch
        $this->eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function ($event) {
                self::assertInstanceOf(PostProjectNodeCreated::class, $event);
                self::assertSame('Test Title', $event->getProjectNode()->getTitle());
                self::assertSame($this->projectSettings, $event->getSettings());

                return true;
            }))
            ->willReturnCallback(static function ($event) {
                return $event;
            });

        // Act
        $this->settingsBuilder->overrideWithInput(new ArrayInput([], $this->inputDefinition));
        $projectNode = $this->settingsBuilder->createProjectNode();

        // Assert
        self::assertInstanceOf(ProjectNode::class, $projectNode);
    }

    public function testCreateProjectNodeUsesModifiedProjectNodeFromEvent(): void
    {
        // Arrange
        $this->projectSettings->setTitle('Original Title');

        // Create a modified project node to return from the event
        $modifiedProjectNode = new ProjectNode(
            'Modified Title',
            '2.0.0',
            'Beta',
            'Modified Copyright',
            $this->now,
        );

        // Setup the event dispatcher to modify the event
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(function ($event) use ($modifiedProjectNode) {
                $modifiedEvent = new PostProjectNodeCreated($modifiedProjectNode, $this->projectSettings);

                return $modifiedEvent;
            });

        // Act
        $this->settingsBuilder->overrideWithInput(new ArrayInput([], $this->inputDefinition));
        $projectNode = $this->settingsBuilder->createProjectNode();

        // Assert
        self::assertSame($modifiedProjectNode, $projectNode);
        self::assertSame('Modified Title', $projectNode->getTitle());
        self::assertSame('2.0.0', $projectNode->getVersion());
    }

    public function testSettingsAreInitializedWithCurrentDirectory(): void
    {
        // Arrange - Call createProjectNode first
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function ($event) {
                return $event;
            });

        $this->settingsBuilder->overrideWithInput(new ArrayInput([], $this->inputDefinition));
        $this->settingsBuilder->createProjectNode();

        // Act
        $settings = $this->settingsBuilder->getSettings();

        // Assert
        self::assertSame($this->currentDir, $settings->getInput());
    }

    public function testSetInputFromArgument(): void
    {
        // Arrange - Use the configured input definition from the dummy command
        $input = new ArrayInput([
            'input' => $this->currentDir,
        ], $this->inputDefinition);

        // Act
        $this->settingsBuilder->overrideWithInput($input);

        // Setup for createProjectNode
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function ($event) {
                return $event;
            });
        $this->settingsBuilder->createProjectNode();

        // Get settings and assert
        $settings = $this->settingsBuilder->getSettings();
        self::assertSame($this->currentDir, $settings->getInput());
    }

    public function testSetOutputFromOption(): void
    {
        // Arrange - Use the configured input definition from the dummy command
        $input = new ArrayInput(['--output' => '/path/to/output'], $this->inputDefinition);

        // Act
        $this->settingsBuilder->overrideWithInput($input);

        // Setup for createProjectNode
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function ($event) {
                return $event;
            });
        $this->settingsBuilder->createProjectNode();

        // Get settings and assert
        $settings = $this->settingsBuilder->getSettings();
        self::assertSame('/path/to/output', $settings->getOutput());
    }

    public function testSetInputFileFromOption(): void
    {
        // Arrange - Use the configured input definition from the dummy command
        $input = new ArrayInput(['--input-file' => 'document.rst'], $this->inputDefinition);

        // Act
        $this->settingsBuilder->overrideWithInput($input);

        // Setup for createProjectNode
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function ($event) {
                return $event;
            });
        $this->settingsBuilder->createProjectNode();

        // Get settings and assert
        $settings = $this->settingsBuilder->getSettings();
        self::assertSame('document', $settings->getInputFile());
        self::assertSame('rst', $settings->getInputFormat());
    }

    public function testSetInputFormatFromOption(): void
    {
        // Arrange - Use the configured input definition from the dummy command
        $input = new ArrayInput(['--input-format' => 'Markdown'], $this->inputDefinition);

        // Act
        $this->settingsBuilder->overrideWithInput($input);

        // Setup for createProjectNode
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function ($event) {
                return $event;
            });
        $this->settingsBuilder->createProjectNode();

        // Get settings and assert
        $settings = $this->settingsBuilder->getSettings();
        self::assertSame('Markdown', $settings->getInputFormat());
    }

    public function testSetLogPathFromOption(): void
    {
        // Arrange - Use the configured input definition from the dummy command
        $input = new ArrayInput(['--log-path' => '/path/to/logs'], $this->inputDefinition);

        // Act
        $this->settingsBuilder->overrideWithInput($input);

        // Setup for createProjectNode
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function ($event) {
                return $event;
            });
        $this->settingsBuilder->createProjectNode();

        // Get settings and assert
        $settings = $this->settingsBuilder->getSettings();
        self::assertSame('/path/to/logs', $settings->getLogPath());
    }

    public function testSetFailOnErrorLevelFromOption(): void
    {
        // Arrange - We need to manually add this option since it's not in configureCommand
        $definition = clone $this->inputDefinition;
        $command = new Command('test');
        $command->addOption('fail-on-error');
        $definition->addOption($command->getDefinition()->getOption('fail-on-error'));

        $input = new ArrayInput(['--fail-on-error' => true], $definition);

        // Act
        $this->settingsBuilder->overrideWithInput($input);

        // Setup for createProjectNode
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function ($event) {
                return $event;
            });
        $this->settingsBuilder->createProjectNode();

        // Get settings and assert
        $settings = $this->settingsBuilder->getSettings();
        self::assertSame(LogLevel::ERROR, $settings->getFailOnError());
    }

    public function testSetFailOnWarningLevelFromOption(): void
    {
        // Arrange - We need to manually add this option since it's not in configureCommand
        $definition = clone $this->inputDefinition;
        $command = new Command('test');
        $command->addOption('fail-on-log');
        $definition->addOption($command->getDefinition()->getOption('fail-on-log'));

        $input = new ArrayInput(['--fail-on-log' => true], $definition);

        // Act
        $this->settingsBuilder->overrideWithInput($input);

        // Setup for createProjectNode
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function ($event) {
                return $event;
            });
        $this->settingsBuilder->createProjectNode();

        // Get settings and assert
        $settings = $this->settingsBuilder->getSettings();
        self::assertSame(LogLevel::WARNING, $settings->getFailOnError());
    }

    public function testSetOutputFormatsFromOption(): void
    {
        // Arrange - Use the configured input definition from the dummy command
        $outputFormats = ['html', 'interlink'];

        $input = new ArrayInput(['--output-format' => $outputFormats], $this->inputDefinition);

        // Act
        $this->settingsBuilder->overrideWithInput($input);

        // Setup for createProjectNode
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function ($event) {
                return $event;
            });
        $this->settingsBuilder->createProjectNode();

        // Get settings and assert
        $settings = $this->settingsBuilder->getSettings();
        self::assertSame($outputFormats, $settings->getOutputFormats());
    }

    public function testSetThemeFromOption(): void
    {
        // Arrange - Use the configured input definition from the dummy command
        $input = new ArrayInput(['--theme' => 'bootstrap'], $this->inputDefinition);

        // Act
        $this->settingsBuilder->overrideWithInput($input);

        // Setup for createProjectNode
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function ($event) {
                return $event;
            });
        $this->settingsBuilder->createProjectNode();

        // Get settings and assert
        $settings = $this->settingsBuilder->getSettings();
        self::assertSame('bootstrap', $settings->getTheme());
    }

    public function testSetProgressBarFromOption(): void
    {
        // Arrange - We need to manually add this option since it's not in configureCommand
        $definition = clone $this->inputDefinition;
        $command = new Command('test');
        $command->addOption('progress');
        $definition->addOption($command->getDefinition()->getOption('progress'));

        $input = new ArrayInput(['--progress' => false], $definition);

        // Act
        $this->settingsBuilder->overrideWithInput($input);

        // Setup for createProjectNode
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function ($event) {
                return $event;
            });
        $this->settingsBuilder->createProjectNode();

        // Get settings and assert
        $settings = $this->settingsBuilder->getSettings();
        self::assertFalse($settings->isShowProgressBar());
    }
}
