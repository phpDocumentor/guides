<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer\UrlGenerator;

use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RelativeUrlGeneratorTest extends TestCase
{
    #[DataProvider('generateRelativeInternalUrlProvider')]
    public function testGenerateRelativeInternalUrl(string $expected, string $canonicalUrl, string $currentFileName): void
    {
        $urlGenerator = new RelativeUrlGenerator(self::createStub(DocumentNameResolverInterface::class));
        $renderContext = $this->createMock(RenderContext::class);
        $renderContext->method('getCurrentFileName')->willReturn($currentFileName);
        $renderContext->method('getOutputFormat')->willReturn('html');
        self::assertSame($expected, $urlGenerator->generateInternalUrl($renderContext, $canonicalUrl));
    }

    /** @return array<string, array<string, string|null>> */
    public static function generateRelativeInternalUrlProvider(): array
    {
        return [
            'Same File' => [
                'expected' => '#',
                'canonicalUrl' => 'directory/file.html',
                'currentPath' => 'directory/file',
            ],
            'Same File with anchor' => [
                'expected' => '#anchor',
                'canonicalUrl' => 'directory/file.html#anchor',
                'currentPath' => 'directory/file',
            ],
            'File in same directory' => [
                'expected' => 'file.html',
                'canonicalUrl' => 'directory/file.html',
                'currentPath' => 'directory/anotherFile',
            ],
            'File in subdirectory' => [
                'expected' => 'subdirectory/file.html',
                'canonicalUrl' => 'directory/subdirectory/file.html',
                'currentPath' => 'directory/anotherFile',
            ],
            'File in directory above' => [
                'expected' => '../file.html',
                'canonicalUrl' => 'file.html',
                'currentPath' => 'directory/anotherFile',
            ],
            'File in two directory above' => [
                'expected' => '../../file.html',
                'canonicalUrl' => 'file.html',
                'currentPath' => 'directory/subdirectory/anotherFile',
            ],
            'File in other subdirectory above' => [
                'expected' => '../subdirectory/file.html',
                'canonicalUrl' => 'directory/subdirectory/file.html',
                'currentPath' => 'directory/othersubdirectory/anotherFile',
            ],
        ];
    }

    #[DataProvider('fileUrlProvider')]
    public function testCreateFileUrl(string $expected, string $filename, string $outputFormat = 'html', string|null $anchor = null, string $skip = ''): void
    {
        if ($skip !== '') {
            self::markTestSkipped($skip);
        }

        $urlGenerator = new RelativeUrlGenerator(self::createStub(DocumentNameResolverInterface::class));
        $renderContext = $this->createMock(RenderContext::class);
        $renderContext->method('getCurrentFileName')->willReturn($filename);
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
}
