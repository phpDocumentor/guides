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
use League\Flysystem\FilesystemInterface;
use phpDocumentor\FileSystem\Finder\Exclude;
use phpDocumentor\Guides\Nodes\ProjectNode;
use PHPUnit\Framework\TestCase;

final class ParseDirectoryCommandTest extends TestCase
{
    public function testNoDeprecationIsTriggeredWhenNoExclusionIsPassed(): void
    {
        $before = Deprecation::getUniqueTriggeredDeprecationsCount();

        $command = new ParseDirectoryCommand(
            $this->createMock(FilesystemInterface::class),
            'docs',
            'rst',
            new ProjectNode(),
        );

        self::assertSame($before, Deprecation::getUniqueTriggeredDeprecationsCount());
        self::assertFalse($command->hasExclude());
        self::assertFalse($command->hasExcludedSpecification());
    }

    public function testNoDeprecationIsTriggeredWhenExcludeIsPassed(): void
    {
        $before = Deprecation::getUniqueTriggeredDeprecationsCount();

        $command = new ParseDirectoryCommand(
            $this->createMock(FilesystemInterface::class),
            'docs',
            'rst',
            new ProjectNode(),
            new Exclude(),
        );

        self::assertSame($before, Deprecation::getUniqueTriggeredDeprecationsCount());
        self::assertTrue($command->hasExclude());
        self::assertFalse($command->hasExcludedSpecification());
    }

    public function testDeprecationIsTriggeredWhenSpecificationInterfaceIsPassed(): void
    {
        $before = Deprecation::getUniqueTriggeredDeprecationsCount();

        $command = new ParseDirectoryCommand(
            $this->createMock(FilesystemInterface::class),
            'docs',
            'rst',
            new ProjectNode(),
            new InPath(new Path('docs')),
        );

        self::assertSame(
            $before + 1,
            Deprecation::getUniqueTriggeredDeprecationsCount(),
        );
        self::assertFalse($command->hasExclude());
        self::assertTrue($command->hasExcludedSpecification());
    }

    public function testGetExcludeReturnsEmptyExcludeWhenNoneWasPassed(): void
    {
        $command = new ParseDirectoryCommand(
            $this->createMock(FilesystemInterface::class),
            'docs',
            'rst',
            new ProjectNode(),
        );

        self::assertEquals(new Exclude(), $command->getExclude());
    }

    public function testGetExcludedSpecificationReturnsTheStoredSpecification(): void
    {
        $specification = new InPath(new Path('docs'));
        $command = new ParseDirectoryCommand(
            $this->createMock(FilesystemInterface::class),
            'docs',
            'rst',
            new ProjectNode(),
            $specification,
        );

        self::assertSame($specification, $command->getExcludedSpecification());
    }
}
