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

namespace phpDocumentor\Guides;

use Doctrine\Deprecations\Deprecation;
use Flyfinder\Path;
use Flyfinder\Specification\InPath;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\FileSystem\Finder\Exclude;
use PHPUnit\Framework\TestCase;

final class FileCollectorTest extends TestCase
{
    public function testCollectDoesNotTriggerDeprecationWhenNoExclusionIsPassed(): void
    {
        $filesystem = $this->createMock(FileSystem::class);
        $filesystem->method('find')->willReturn([]);

        $before = Deprecation::getUniqueTriggeredDeprecationsCount();

        (new FileCollector())->collect($filesystem, 'docs', 'rst');

        self::assertSame($before, Deprecation::getUniqueTriggeredDeprecationsCount());
    }

    public function testCollectDoesNotTriggerDeprecationWhenExcludeIsPassed(): void
    {
        $filesystem = $this->createMock(FileSystem::class);
        $filesystem->method('find')->willReturn([]);

        $before = Deprecation::getUniqueTriggeredDeprecationsCount();

        (new FileCollector())->collect($filesystem, '', 'rst', new Exclude());

        self::assertSame($before, Deprecation::getUniqueTriggeredDeprecationsCount());
    }

    public function testCollectTriggersDeprecationWhenSpecificationInterfaceIsPassed(): void
    {
        $filesystem = $this->createMock(FileSystem::class);
        $filesystem->method('find')->willReturn([]);

        $before = Deprecation::getUniqueTriggeredDeprecationsCount();

        (new FileCollector())->collect(
            $filesystem,
            'docs',
            'rst',
            new InPath(new Path('docs')),
        );

        self::assertSame(
            $before + 1,
            Deprecation::getUniqueTriggeredDeprecationsCount(),
        );
    }
}
