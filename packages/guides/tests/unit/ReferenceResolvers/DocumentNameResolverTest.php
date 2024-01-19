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

namespace phpDocumentor\Guides\ReferenceResolvers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DocumentNameResolverTest extends TestCase
{
    #[DataProvider('canonicalUrlProvider')]
    public function testCanonicalUrl(string $basePath, string $url, string $result): void
    {
        $documentNameResolver = new DocumentNameResolver();
        self::assertSame($result, $documentNameResolver->canonicalUrl($basePath, $url));
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
        $documentNameResolver = new DocumentNameResolver();
        self::assertSame($result, $documentNameResolver->absoluteUrl($basePath, $url));
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
}
