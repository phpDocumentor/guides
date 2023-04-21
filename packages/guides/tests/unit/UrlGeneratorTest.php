<?php

declare(strict_types=1);

namespace unit;

use phpDocumentor\Guides\UrlGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UrlGeneratorTest extends TestCase
{
    #[DataProvider('cannicalUrlProvider')]
    public function testCannicalUrl(string $basePath, string $url, string $result): void
    {
        $urlGenerator = new UrlGenerator();
        self::assertSame($result, $urlGenerator->canonicalUrl($basePath, $url));
    }

    /** @return string[][] */
    public static function cannicalUrlProvider(): array
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

    public function testUrlGenerationOfInvalidUrlReturnsInput(): void
    {
        $urlGenerator = new UrlGenerator();
        self::assertSame('tcp://hostname:port', $urlGenerator->generateUrl('tcp://hostname:port'));
    }
}
