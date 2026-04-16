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
use League\Tactician\CommandBus;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\FileSystem\Finder\Exclude;
use phpDocumentor\Guides\FileCollector;
use phpDocumentor\Guides\Nodes\ProjectNode;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

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
