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

namespace phpDocumentor\Guides\Handlers;

use Doctrine\Deprecations\Deprecation;
use Flyfinder\Path;
use Flyfinder\Specification\InPath;
use League\Flysystem\Filesystem as LeagueFilesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Tactician\CommandBus;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\FileSystem\Finder\Exclude;
use phpDocumentor\FileSystem\FlySystemAdapter;
use phpDocumentor\Guides\Event\PostCollectFilesForParsingEvent;
use phpDocumentor\Guides\FileCollector;
use phpDocumentor\Guides\Files;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

use function assert;
use function ksort;
use function str_starts_with;

final class ParseDirectoryHandlerTest extends TestCase
{
    public function testHandlerForwardsExcludeToCollectorWithoutTriggeringSpecificationDeprecation(): void
    {
        $command = $this->createCommand(new Exclude(['/excluded']));
        $handler = $this->createHandler();

        $before = Deprecation::getUniqueTriggeredDeprecationsCount();

        $handler->handle($command);

        self::assertSame($before, Deprecation::getUniqueTriggeredDeprecationsCount());
    }

    public function testHandlerDoesNotTriggerDeprecationWhenNoExclusionIsConfigured(): void
    {
        $command = $this->createCommand();
        $handler = $this->createHandler();

        $before = Deprecation::getUniqueTriggeredDeprecationsCount();

        $handler->handle($command);

        self::assertSame($before, Deprecation::getUniqueTriggeredDeprecationsCount());
    }

    public function testHandlerForwardsLegacySpecificationWithoutFiringExtraDeprecation(): void
    {
        $command = $this->createCommand(new InPath(new Path('docs')));
        $handler = $this->createHandler();

        $before = Deprecation::getUniqueTriggeredDeprecationsCount();

        $handler->handle($command);

        self::assertSame(
            $before,
            Deprecation::getUniqueTriggeredDeprecationsCount(),
            'Internal handler dispatch must not re-fire the deprecation already raised at construction.',
        );
    }

    public function testEachFileIsParsedWithTheFormatOfItsExtension(): void
    {
        $parsed = $this->parseDirectory(['index.md' => '', 'setup.rst' => '', 'specs/spec.md' => ''], 'rst,md');

        self::assertSame(['index' => 'md', 'setup' => 'rst', 'specs/spec' => 'md'], $parsed);
    }

    public function testTheIndexFileCanBeInAnyOfTheInputFormats(): void
    {
        $roots = [];
        $this->parseDirectory(['index.md' => '', 'setup.rst' => ''], 'rst,md', static function (ParseFileCommand $command) use (&$roots): void {
            if (!$command->isRoot()) {
                return;
            }

            $roots[] = $command->getFile() . '.' . $command->getExtension();
        });

        self::assertSame(['index.md'], $roots);
    }

    public function testAListenerThatFiltersTheFilesWithoutTheirExtensionsKeepsTheParserOfEachFile(): void
    {
        $parsed = $this->parseDirectory(
            ['index.rst' => '', 'spec.md' => '', 'Localization.de_DE/index.md' => ''],
            'rst,md',
            null,
            static function (PostCollectFilesForParsingEvent $event): void {
                $files = new Files();
                foreach ($event->getFiles() as $file) {
                    if (str_starts_with($file, 'Localization.')) {
                        continue;
                    }

                    $files->add($file);
                }

                $event->setFiles($files);
            },
        );

        self::assertSame(['index' => 'rst', 'spec' => 'md'], $parsed);
    }

    public function testAFileAddedByAListenerWithoutExtensionIsParsedWithTheFirstInputFormat(): void
    {
        $parsed = $this->parseDirectory(
            ['index.rst' => '', 'spec.md' => ''],
            'rst,md',
            null,
            static function (PostCollectFilesForParsingEvent $event): void {
                $event->getFiles()->add('generated');
            },
        );

        self::assertSame(['generated' => 'rst', 'index' => 'rst', 'spec' => 'md'], $parsed);
    }

    /**
     * @param array<string, string> $files
     * @param (callable(ParseFileCommand): void)|null $onParse
     * @param (callable(PostCollectFilesForParsingEvent): void)|null $onCollect
     *
     * @return array<string, string> the extension each document was parsed with, by document name
     */
    private function parseDirectory(array $files, string $inputFormat, callable|null $onParse = null, callable|null $onCollect = null): array
    {
        $filesystem = new LeagueFilesystem(new InMemoryFilesystemAdapter());
        foreach ($files as $path => $contents) {
            $filesystem->write($path, $contents);
        }

        $parsed = [];
        $commandBus = $this->createMock(CommandBus::class);
        $commandBus->method('handle')->willReturnCallback(
            static function (object $command) use (&$parsed, $onParse): DocumentNode {
                assert($command instanceof ParseFileCommand);
                $parsed[$command->getFile()] = $command->getExtension();
                if ($onParse !== null) {
                    $onParse($command);
                }

                return new DocumentNode('hash', $command->getFile());
            },
        );

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnCallback(
            static function (object $event) use ($onCollect): object {
                if ($onCollect !== null && $event instanceof PostCollectFilesForParsingEvent) {
                    $onCollect($event);
                }

                return $event;
            },
        );

        $handler = new ParseDirectoryHandler(new FileCollector(), $commandBus, $eventDispatcher);
        $handler->handle(new ParseDirectoryCommand(
            FlySystemAdapter::createFromFileSystem($filesystem),
            '',
            $inputFormat,
            new ProjectNode(),
            new Exclude(),
        ));

        ksort($parsed);

        return $parsed;
    }

    private function createCommand(Exclude|InPath|null $exclusion = null): ParseDirectoryCommand
    {
        $filesystem = $this->createMock(FileSystem::class);
        $filesystem->method('listContents')->willReturn([['basename' => 'index.rst']]);
        $filesystem->method('find')->willReturn([]);

        return new ParseDirectoryCommand(
            $filesystem,
            '',
            'rst',
            new ProjectNode(),
            $exclusion,
        );
    }

    private function createHandler(): ParseDirectoryHandler
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnCallback(
            static fn (object $event): object => $event,
        );

        return new ParseDirectoryHandler(
            new FileCollector(),
            $this->createMock(CommandBus::class),
            $eventDispatcher,
        );
    }
}
