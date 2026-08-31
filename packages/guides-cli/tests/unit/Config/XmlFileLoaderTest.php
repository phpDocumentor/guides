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

namespace phpDocumentor\Guides\Cli\Config;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;

use function count;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * The integration fixtures render a whole site and prove what a reader sees. This covers the shapes
 * an author is unlikely to write but the loader handles explicitly, and pins which attributes the
 * backward-compatible quote stripping is allowed to touch.
 */
final class XmlFileLoaderTest extends TestCase
{
    private string|null $file = null;

    protected function tearDown(): void
    {
        if ($this->file === null) {
            return;
        }

        unlink($this->file);
        $this->file = null;
    }

    /** @param array<string, string> $expected */
    #[DataProvider('provideProjectElements')]
    public function testReadsProjectAttributes(string $projectElement, array $expected): void
    {
        $root = $this->loadRoot($projectElement);

        // Asserted separately from the contents: an empty project config and a missing one are two
        // different states, and `?? []` would report them as the same.
        self::assertArrayHasKey('project', $root);
        self::assertSame($expected, $root['project']);
    }

    /** @return iterable<string, array{string, array<string, string>}> */
    public static function provideProjectElements(): iterable
    {
        // phpize() would turn this into the float 0.1. The DOM keeps the digit.
        yield 'a version is read as written' => [
            '<project title="T" version="0.10" release="0.10.0"/>',
            ['title' => 'T', 'version' => '0.10', 'release' => '0.10.0'],
        ];

        // release gets no special treatment beyond version, and a release is not always longer than
        // the version it belongs to: `1.0` is the shape phpize turns into the int 1.
        yield 'a trailing zero survives in release as well' => [
            '<project version="1.0" release="1.0"/>',
            ['version' => '1.0', 'release' => '1.0'],
        ];

        // The workaround for the coercion this branch removes; still honoured for files that adopted it.
        yield 'single quotes are stripped from version and release' => [
            '<project version="\'3.0\'" release="\'3.0.0\'"/>',
            ['version' => '3.0', 'release' => '3.0.0'],
        ];

        // The stripping is backward compatibility for two attributes, not a general unquoting rule:
        // a title that really is quoted keeps its quotes.
        yield 'single quotes are kept everywhere else' => [
            '<project title="\'T\'" copyright="\'2026\'"/>',
            ['title' => "'T'", 'copyright' => "'2026'"],
        ];

        yield 'an attribute-less project yields an empty project config' => [
            '<project/>',
            [],
        ];
    }

    /**
     * Detaching <project> from a file that has no other child leaves convertDomElementToArray() with an
     * empty element, for which it returns null rather than an array.
     */
    public function testAProjectOnlyFileStillYieldsItsProject(): void
    {
        self::assertSame(
            ['version' => '0.10'],
            $this->loadRoot('<project version="0.10"/>')['project'] ?? [],
        );
    }

    /** The schema lets an extension carry arbitrary children; a <project> among them is not the project. */
    public function testAProjectInsideAnExtensionIsNotRead(): void
    {
        $root = $this->loadRoot(
            '<extension class="Some\Extension"><project version="9.9"/></extension>'
            . '<project version="0.10"/>',
        );

        self::assertSame(['version' => '0.10'], $root['project'] ?? []);
    }

    public function testAFileWithoutAProjectHasNoProjectKey(): void
    {
        self::assertArrayNotHasKey('project', $this->loadRoot(''));
    }

    /**
     * Loads a guides.xml built around $body and returns the config of the file itself, which the loader
     * appends last.
     *
     * @return array<string, mixed>
     */
    private function loadRoot(string $body): array
    {
        $file = tempnam(sys_get_temp_dir(), 'guides-xml-');
        self::assertIsString($file);
        $this->file = $file;

        file_put_contents(
            $file,
            '<?xml version="1.0" encoding="UTF-8" ?>'
            . '<guides xmlns="https://www.phpdoc.org/guides">' . $body . '</guides>',
        );

        $configs = (new XmlFileLoader(new FileLocator()))->load($file, 'xml');

        return $configs[count($configs) - 1];
    }
}
