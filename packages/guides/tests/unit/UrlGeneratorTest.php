<?php

declare(strict_types=1);

namespace phpDocumentor\Guides;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UrlGeneratorTest extends TestCase
{
    #[DataProvider('fileUrlProvider')]
    public function testCreateFileUrl(string $expected, string $filename, string $outputFormat = 'html', string|null $anchor = null, string $skip = ''): void
    {
        if ($skip !== '') {
            self::markTestSkipped($skip);
        }

        $urlGenerator = new UrlGenerator();
        self::assertSame($expected, $urlGenerator->createFileUrl($filename, $outputFormat, $anchor));
    }

    /** @return array<string, array<string, string|null>> */
    public static function fileUrlProvider(): array
    {
        return [
            'Simple Filename' => [
                'expected' => 'file.html',
                'filename' => 'file',
            ],
            'Complex Filename' => [
                'expected' => 'file-something.html',
                'filename' => 'file-something',
            ],
            'Output Format' => [
                'expected' => 'texfile.tex',
                'filename' => 'texfile',
                'outputFormat' => 'tex',
            ],
            'File with anchor' => [
                'expected' => 'file.html#anchor',
                'filename' => 'file',
                'outputFormat' => 'html',
                'anchor' => 'anchor',
            ],
            'Empty File with anchor' => [
                'expected' => '#anchor',
                'filename' => '',
                'outputFormat' => 'html',
                'anchor' => 'anchor',
                'skip' => 'Empty filenames are not supported',
            ],
            'Empty File with empty anchor' => [
                'expected' => '#',
                'filename' => '',
                'outputFormat' => 'html',
                'anchor' => null,
                'skip' => 'Empty filenames are not supported',
            ],
        ];
    }

    #[DataProvider('canonicalUrlProvider')]
    public function testCanonicalUrl(string $basePath, string $url, string $result): void
    {
        $urlGenerator = new UrlGenerator();
        self::assertSame($result, $urlGenerator->canonicalUrl($basePath, $url));
    }

    /** @return string[][] */
    public static function canonicalUrlProvider(): array
    {
        return [
            [
                'basePath' => 'dir',
                'url' => 'file',
                'result' => 'dir/file',
            ],
            [
                'basePath' => 'dir',
                'url' => '../file',
                'result' => 'file',
            ],
            [
                'basePath' => 'dir/subdir',
                'url' => '../file',
                'result' => 'dir/file',
            ],
            [
                'basePath' => 'dir/subdir',
                'url' => '../../file',
                'result' => 'file',
            ],
            [
                'basePath' => 'dir/subdir',
                'url' => '.././file',
                'result' => 'dir/file',
            ],
            [
                'basePath' => 'dir/subdir',
                'url' => './file',
                'result' => 'dir/subdir/file',
            ],
        ];
    }

    #[DataProvider('abstractUrlProvider')]
    public function testAbsoluteUrl(string $basePath, string $url, string $result): void
    {
        $urlGenerator = new UrlGenerator();
        self::assertSame($result, $urlGenerator->absoluteUrl($basePath, $url));
    }

    /** @return string[][] */
    public static function abstractUrlProvider(): array
    {
        return [
            [
                'basePath' => '/',
                'url' => 'file',
                'result' => '/file',
            ],
            [
                'basePath' => '/foo',
                'url' => '/file',
                'result' => '/file',
            ],
            [
                'basePath' => '/dir',
                'url' => 'file',
                'result' => '/dir/file',
            ],
            [
                'basePath' => '/dir/',
                'url' => 'file',
                'result' => '/dir/file',
            ],
        ];
    }

    #[DataProvider('documentPathProvider')]
    public function testRelativeDocUrl(
        string $currentDirectory,
        string $destinationPath,
        bool $validDocumentEntry,
        string $linkedDocument,
        string $result,
        string|null $anchor = null,
    ): void {
        $urlGenerator = new UrlGenerator();
        self::assertSame($result, $urlGenerator->generateOutputUrlFromDocumentPath(
            $currentDirectory,
            $destinationPath,
            $validDocumentEntry,
            $linkedDocument,
            'txt',
            $anchor,
        ));
    }

    /** @return array<string, array<string, bool|string>> */
    public static function documentPathProvider(): array
    {
        return [
            'relative document' => [
                'currentDirectory' => 'getting-started',
                'destinationPath' => 'guide',
                'validDocumentEntry' => false,
                'linkedDocument' => 'installing',
                'result' => 'guide/getting-started/installing.txt',
            ],
            'absolute document path' => [
                'currentDirectory' => 'getting-started',
                'destinationPath' => 'guide',
                'validDocumentEntry' => false,
                'linkedDocument' => '/installing',
                'result' => 'guide/installing.txt',
            ],
            'relative document path with anchor' => [
                'currentDirectory' => 'getting-started',
                'destinationPath' => 'guide',
                'validDocumentEntry' => true,
                'linkedDocument' => 'getting-started/configuration',
                'result' => 'guide/getting-started/configuration.txt#composer',
                'anchor' => 'composer',
            ],
            'relative document path up in directory' => [
                'currentDirectory' => 'getting-started',
                'destinationPath' => 'guide',
                'validDocumentEntry' => false,
                'linkedDocument' => '../references/installing',
                'result' => 'guide/references/installing.txt',
            ],
        ];
    }
}
