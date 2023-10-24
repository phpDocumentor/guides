<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer\UrlGenerator;

use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AbsoluteUrlGeneratorTest extends TestCase
{
    #[DataProvider('generateAbsoluteInternalUrlProvider')]
    public function testGenerateAbsoluteInternalUrl(string $expected, string $canonicalUrl, string $destinationPath): void
    {
        $urlGenerator = new AbsoluteUrlGenerator();
        $renderContext = $this->createMock(RenderContext::class);
        $renderContext->method('getOutputFolder')->willReturn($destinationPath);
        self::assertSame($expected, $urlGenerator->generateInternalPathFromRelativeUrl($renderContext, $canonicalUrl));
    }

    /** @return array<string, array<string, string|null>> */
    public static function generateAbsoluteInternalUrlProvider(): array
    {
        return [
            'Destination path absolute' => [
                'expected' => '/guides/file.html',
                'canonicalUrl' => 'file.html',
                'destinationPath' => '/guides',
            ],
            'Destination path relative' => [
                'expected' => 'guides/dir/file.html',
                'canonicalUrl' => 'dir/file.html',
                'destinationPath' => 'guides',
            ],
            'Destination path relative slash at end' => [
                'expected' => 'guides/dir/file.html',
                'canonicalUrl' => 'dir/file.html',
                'destinationPath' => 'guides/',
            ],
            'Destination Path Empty' => [
                'expected' => 'dir/file.html',
                'canonicalUrl' => 'dir/file.html',
                'destinationPath' => '',
            ],
            'Destination Path Slash' => [
                'expected' => '/dir/file.html',
                'canonicalUrl' => 'dir/file.html',
                'destinationPath' => '/',
            ],
        ];
    }
}
