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

namespace phpDocumentor\FileSystem\Finder;

use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(WithinPath::class)]
final class WithinPathTest extends TestCase
{
    #[DataProvider('provideSpecifications')]
    public function testIsSatisfiedByThePathAndEverythingBelowIt(string $specification, string $path, bool $expected): void
    {
        self::assertSame($expected, (new WithinPath($specification))->isSatisfiedBy(new FileAttributes($path)));
        self::assertSame($expected, (new WithinPath($specification))->isSatisfiedBy(['path' => $path]));
    }

    /** @return iterable<string, array{string, string, bool}> */
    public static function provideSpecifications(): iterable
    {
        yield 'the directory itself' => ['_build', '_build', true];
        yield 'a file below' => ['_build', '_build/vendor/twig/twig/README.rst', true];
        yield 'with slashes around the path' => ['/_build/', '_build/index.rst', true];
        yield 'a file of the same name' => ['docs/old.rst', 'docs/old.rst', true];
        yield 'a name that starts the same' => ['_build', '_build-old/index.rst', false];
        yield 'a directory above' => ['docs/_build', 'docs/index.rst', false];
        yield 'dots are no wildcards' => ['a.b', 'axb/index.rst', false];
    }

    public function testLetsTheFinderSkipAMatchingDirectory(): void
    {
        $specification = new WithinPath('docs/_build');

        self::assertTrue($specification->willBeSatisfiedByEverythingBelow(new DirectoryAttributes('docs/_build')));
        self::assertFalse($specification->willBeSatisfiedByEverythingBelow(new DirectoryAttributes('docs')));
    }

    public function testCanBeSatisfiedBelowTheDirectoriesAboveThePath(): void
    {
        $specification = new WithinPath('docs/_build');

        self::assertTrue($specification->canBeSatisfiedBySomethingBelow(new DirectoryAttributes('')));
        self::assertTrue($specification->canBeSatisfiedBySomethingBelow(new DirectoryAttributes('docs')));
        self::assertTrue($specification->canBeSatisfiedBySomethingBelow(new DirectoryAttributes('docs/_build/html')));
        self::assertFalse($specification->canBeSatisfiedBySomethingBelow(new DirectoryAttributes('src')));
    }
}
