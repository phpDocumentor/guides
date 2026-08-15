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

use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function array_map;
use function explode;
use function is_array;
use function sort;
use function str_starts_with;
use function stripslashes;
use function substr;

final class ExternalReferenceResolverTest extends TestCase
{
    /**
     * The schemes exist twice: as the alternation of the deprecated `SUPPORTED_SCHEMAS` regex, which
     * `InlineLexer` still builds its URL detection from, and as the list behind `isSupportedScheme()`,
     * which decides whether the resolver accepts what the lexer found. A scheme added to one only makes
     * the lexer produce a link the resolver then refuses, or the reverse — so they have to agree.
     */
    public function testTheRegexAndTheListHoldTheSameSchemes(): void
    {
        $fromRegex = self::schemesFromRegex();
        $fromList = self::schemesFromList();

        sort($fromRegex);
        sort($fromList);

        self::assertSame($fromList, $fromRegex);
    }

    public function testEverySchemeOfTheRegexIsAccepted(): void
    {
        foreach (self::schemesFromRegex() as $scheme) {
            self::assertTrue(
                ExternalReferenceResolver::isSupportedScheme($scheme),
                'Scheme "' . $scheme . '" is detected by the lexer but not accepted by the resolver',
            );
        }
    }

    /** @return string[] */
    private static function schemesFromRegex(): array
    {
        $pattern = ExternalReferenceResolver::SUPPORTED_SCHEMAS;
        self::assertTrue(str_starts_with($pattern, '(?:'));

        // Strip the enclosing non-capturing group, then undo the regex escaping of `+`, `.` and `()`.
        return array_map(stripslashes(...), explode('|', substr($pattern, 3, -1)));
    }

    /** @return string[] */
    private static function schemesFromList(): array
    {
        /** @var string[] $list */
        $list = (new ReflectionClass(ExternalReferenceResolver::class))->getConstant('SUPPORTED_SCHEMAS_LIST');
        self::assertTrue(is_array($list));

        return $list;
    }
}
