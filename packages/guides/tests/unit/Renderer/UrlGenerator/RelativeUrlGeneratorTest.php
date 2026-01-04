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

namespace phpDocumentor\Guides\Renderer\UrlGenerator;

use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RelativeUrlGeneratorTest extends TestCase
{
    #[DataProvider('generateRelativeInternalUrlProvider')]
    public function testGenerateRelativeInternalUrl(string $expected, string $canonicalUrl, string $outputFilePath): void
    {
        $urlGenerator = new RelativeUrlGenerator(self::createStub(DocumentNameResolverInterface::class));
        $renderContext = $this->createMock(RenderContext::class);
        $renderContext->method('getOutputFilePath')->willReturn($outputFilePath);
        self::assertSame($expected, $urlGenerator->generateInternalUrl($renderContext, $canonicalUrl));
    }

    /** @return array<string, array<string, string|null>> */
    public static function generateRelativeInternalUrlProvider(): array
    {
        return [
            'Same File' => [
                'expected' => '#',
                'canonicalUrl' => 'directory/file.html',
                'outputFilePath' => 'directory/file.html',
            ],
            'Same File with anchor' => [
                'expected' => '#anchor',
                'canonicalUrl' => 'directory/file.html#anchor',
                'outputFilePath' => 'directory/file.html',
            ],
            'File in same directory' => [
                'expected' => 'file.html',
                'canonicalUrl' => 'directory/file.html',
                'outputFilePath' => 'directory/anotherFile.html',
            ],
            'File in subdirectory' => [
                'expected' => 'subdirectory/file.html',
                'canonicalUrl' => 'directory/subdirectory/file.html',
                'outputFilePath' => 'directory/anotherFile.html',
            ],
            'File in directory above' => [
                'expected' => '../file.html',
                'canonicalUrl' => 'file.html',
                'outputFilePath' => 'directory/anotherFile.html',
            ],
            'File in two directory above' => [
                'expected' => '../../file.html',
                'canonicalUrl' => 'file.html',
                'outputFilePath' => 'directory/subdirectory/anotherFile',
            ],
            'File in other subdirectory above' => [
                'expected' => '../subdirectory/file.html',
                'canonicalUrl' => 'directory/subdirectory/file.html',
                'outputFilePath' => 'directory/othersubdirectory/anotherFile.html',
            ],
        ];
    }

    #[DataProvider('fileUrlProvider')]
    public function testCreateFileUrl(string $expected, string $filename, string $outputFormat = 'html', string|null $anchor = null): void
    {
        $urlGenerator = new RelativeUrlGenerator(self::createStub(DocumentNameResolverInterface::class));
        $renderContext = $this->createMock(RenderContext::class);
        $renderContext->method('getOutputFormat')->willReturn($outputFormat);
        self::assertSame($expected, $urlGenerator->createFileUrl($renderContext, $filename, $anchor));
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
            ],
            'Empty File with null anchor' => [
                'expected' => '#',
                'filename' => '',
                'outputFormat' => 'html',
                'anchor' => null,
            ],
            'Empty File with empty string anchor' => [
                'expected' => '#',
                'filename' => '',
                'outputFormat' => 'html',
                'anchor' => '',
            ],
            'File with empty string anchor' => [
                'expected' => 'file.html',
                'filename' => 'file',
                'outputFormat' => 'html',
                'anchor' => '',
            ],
        ];
    }
}
