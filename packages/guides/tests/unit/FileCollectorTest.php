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
use RuntimeException;

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

    public function testCollectRecordsTheExtensionOfEachDocumentWhenCollectingSeveralExtensions(): void
    {
        $filesystem = new LeagueFilesystem(new InMemoryFilesystemAdapter());
        $filesystem->write('index.rst', '');
        $filesystem->write('specs/spec.md', '');
        $filesystem->write('notes.txt', '');

        $files = (new FileCollector())->collect(
            FlySystemAdapter::createFromFileSystem($filesystem),
            '',
            ['rst', 'md'],
            new Exclude(),
        );

        self::assertSame(['index', 'specs/spec'], $this->sorted($files));
        self::assertSame('rst', $files->getExtension('index'));
        self::assertSame('md', $files->getExtension('specs/spec'));
    }

    public function testCollectFailsWhenTwoFilesOfDifferentExtensionsAreTheSameDocument(): void
    {
        $filesystem = new LeagueFilesystem(new InMemoryFilesystemAdapter());
        $filesystem->write('index.rst', '');
        $filesystem->write('guide/setup.rst', '');
        $filesystem->write('guide/setup.md', '');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('~"guide/setup\.(rst|md)" and "guide/setup\.(rst|md)" are both the document "guide/setup"~');

        (new FileCollector())->collect(
            FlySystemAdapter::createFromFileSystem($filesystem),
            '',
            ['rst', 'md'],
            new Exclude(),
        );
    }

    public function testCollectWithOneExtensionLeavesFilesOfOtherExtensionsAlone(): void
    {
        $filesystem = new LeagueFilesystem(new InMemoryFilesystemAdapter());
        $filesystem->write('index.rst', '');
        $filesystem->write('index.md', '');

        $files = (new FileCollector())->collect(
            FlySystemAdapter::createFromFileSystem($filesystem),
            '',
            'rst',
            new Exclude(),
        );

        self::assertSame(['index'], $this->sorted($files));
        self::assertSame('rst', $files->getExtension('index'));
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
