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

use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ExternalUrlGeneratorTest extends TestCase
{
    #[DataProvider('urlProvider')]
    public function testGenerateInternalUrl(string $baseUrl, string $canonicalUrl, string $expected): void
    {
        $urlGenerator = new ExternalUrlGenerator(self::createStub(UrlGeneratorInterface::class), $baseUrl);

        $url = $urlGenerator->generateInternalUrl(self::createStub(RenderContext::class), $canonicalUrl);
        self::assertSame($expected, $url);
    }

    /** @return iterable<string, array<string>> */
    public static function urlProvider(): iterable
    {
        yield 'root base, relative path' => ['https://cdn.phpdoc.org', 'img/logo.svg', 'https://cdn.phpdoc.org/img/logo.svg'];
        yield 'root base, absolute path' => ['https://cdn.phpdoc.org', '/img/logo.svg', 'https://cdn.phpdoc.org/img/logo.svg'];

        yield 'path base, relative path' => ['https://cdn.phpdoc.org/assets/', 'img/logo.svg', 'https://cdn.phpdoc.org/assets/img/logo.svg'];
        yield 'path base, absolute path' => ['https://cdn.phpdoc.org/assets/', '/img/logo.svg', 'https://cdn.phpdoc.org/img/logo.svg'];

        yield 'non-absolute base' => ['/assets/', 'img/logo.svg', '/assets/img/logo.svg'];
    }
}
