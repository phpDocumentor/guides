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
use League\Flysystem\Filesystem as LeagueFilesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\FileSystem\Finder\Exclude;
use phpDocumentor\FileSystem\FlySystemAdapter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function sort;

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

    /** @param list<string> $excludedPaths */
    #[DataProvider('provideDirectoryExclusions')]
    public function testCollectExcludesADirectoryWithEverythingInIt(array $excludedPaths): void
    {
        $filesystem = new LeagueFilesystem(new InMemoryFilesystemAdapter());
        $filesystem->write('index.rst', '');
        $filesystem->write('_build/index.rst', '');
        $filesystem->write('_build/vendor/twig/twig/README.rst', '');
        $filesystem->write('_build-notes.rst', '');

        $files = (new FileCollector())->collect(
            FlySystemAdapter::createFromFileSystem($filesystem),
            '',
            'rst',
            new Exclude($excludedPaths),
        );

        self::assertSame(['_build-notes', 'index'], $this->sorted($files));
    }

    /** @return iterable<string, array{list<string>}> */
    public static function provideDirectoryExclusions(): iterable
    {
        yield 'directory name' => [['_build']];
        yield 'directory name with slash' => [['_build/']];
        yield 'all files below the directory' => [['_build/**/*']];
    }

    /** @return list<string> */
    private function sorted(Files $files): array
    {
        $names = [];
        foreach ($files as $file) {
            $names[] = $file;
        }

        sort($names);

        return $names;
    }
}
