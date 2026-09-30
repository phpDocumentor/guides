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

use Generator;
use League\Uri\BaseUri;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\Exception\InvalidUrlException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

use function chr;
use function count;
use function json_encode;
use function str_repeat;
use function strlen;
use function substr;

use const JSON_INVALID_UTF8_SUBSTITUTE;

/**
 * Every input has to end as it did when generateInternalUrl() had League\Uri parse each one: the
 * computed path for a relative path, InvalidUrlException for anything else, and League's own exception
 * where it refuses the input.
 */
final class AbstractUrlGeneratorIsRelativeUrlTest extends TestCase
{
    #[DataProvider('urlProvider')]
    public function testUrlEndsAsLeagueUriDecides(string $url): void
    {
        self::assertSame(self::expected($url), self::actual($url));
    }

    /** @return Generator<string, array{string}> */
    public static function urlProvider(): Generator
    {
        $urls = [
            'empty' => '',
            'file' => 'index.html',
            'file in directory' => 'directory/file.html',
            'file with anchor' => 'directory/file.html#anchor',
            'anchor only' => '#anchor',
            'query only' => '?query',
            'dot segment' => './file.html',
            'parent segment' => '../file.html',
            'dot' => '.',
            'dot dot' => '..',
            'absolute path' => '/file.html',
            'network path' => '//host/file.html',
            'file scheme' => 'file:///tmp/x',
            'https scheme' => 'https://example.org/',
            'mailto scheme' => 'mailto:someone@example.org',
            'drive letter' => 'C:\\file',
            'bare scheme' => 'a:b',
            'colon after slash' => 'directory/a:b.html',
            'colon in anchor' => 'file.html#a:b',
            'colon in query' => 'file.html?a=b:c',
            'trailing newline' => "file.html\n",
            'trailing newline after one character' => "~\n",
            'space' => 'a b.html',
            'newline inside' => "a\nb.html",
            'tab' => "a\tb.html",
            'delete' => "a\x7fb",
            'utf-8' => 'ä.html',
            'invalid utf-8' => "\xff.html",
            'invalid percent encoding' => '%zz',
            'percent encoding' => '%41.html',
            'bracket' => '[',
            'brackets' => 'a[1].html',
            'at sign' => 'a@b',
            'quote' => "a'b",
            'less than' => 'a<b',
            'pipe' => 'a|b',
            'braces' => 'a{b}',
            'backslash' => 'a\\b',
            'tilde' => '~a',
            'dash' => '-a',
            'two anchors' => 'file.html#a#b',
            'long path' => str_repeat('directory/', 10_000) . 'file.html#anchor',
            'one megabyte' => str_repeat('a', 1_000_000),
        ];

        foreach ($urls as $name => $url) {
            yield $name => [$url];
        }
    }

    public function testEveryByteEndsAsLeagueUriDecides(): void
    {
        for ($byte = 0; $byte < 256; $byte++) {
            foreach (['x' . chr($byte) . 'y', chr($byte) . 'x'] as $url) {
                self::assertSame(self::expected($url), self::actual($url), 'for ' . json_encode($url, JSON_INVALID_UTF8_SUBSTITUTE));
            }
        }
    }

    /**
     * Random strings over every printable ASCII character that is not alphanumeric, weighted towards
     * what decides the outcome. A local generator with a fixed seed makes a failure repeat without
     * touching the global mt_rand() state of other tests.
     */
    public function testRandomUrlsEndAsLeagueUriDecides(): void
    {
        $alphabet = [
            'a',
            'Z',
            '0',
            '.',
            '-',
            '_',
            '~',
            '/',
            '/',
            ':',
            ':',
            '#',
            '?',
            '%',
            '[',
            ']',
            '@',
            '\\',
            '!',
            '$',
            '&',
            "'",
            '(',
            ')',
            '*',
            '+',
            ',',
            ';',
            '=',
            '<',
            '>',
            '"',
            '^',
            '`',
            '{',
            '|',
            '}',
            ' ',
            "\t",
            "\n",
            "\x7f",
            "\xc3\xa4",
            "\xff",
        ];
        $state = 1398;
        $next = static function (int $bound) use (&$state): int {
            $state = ($state * 1_103_515_245 + 12_345) & 0x7fffffff;

            return $state % $bound;
        };

        for ($i = 0; $i < 5000; $i++) {
            $url = '';
            $length = $next(41);
            while (strlen($url) < $length) {
                $url .= $alphabet[$next(count($alphabet))];
            }

            self::assertSame(self::expected($url), self::actual($url), 'for ' . json_encode(substr($url, 0, 80), JSON_INVALID_UTF8_SUBSTITUTE));
        }
    }

    private static function actual(string $url): string
    {
        $generator = new class (self::createStub(DocumentNameResolverInterface::class)) extends AbstractUrlGenerator {
            public function generateInternalPathFromRelativeUrl(RenderContext $renderContext, string $canonicalUrl): string
            {
                return 'computed:' . $canonicalUrl;
            }
        };

        return self::outcome(static fn (): string => $generator->generateInternalUrl(self::createStub(RenderContext::class), $url));
    }

    private static function expected(string $url): string
    {
        return self::outcome(static function () use ($url): string {
            if (!BaseUri::from($url)->isRelativePath()) {
                throw new InvalidUrlException('not relative');
            }

            return 'computed:' . $url;
        });
    }

    /** @param callable(): string $call */
    private static function outcome(callable $call): string
    {
        try {
            return $call();
        } catch (Throwable $exception) {
            return $exception::class;
        }
    }
}
