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

namespace phpDocumentor\Guides\RestructuredText\Parser;

use function count;
use function in_array;
use function mb_strlen;
use function preg_match;
use function trim;

final class LineChecker
{
    /**
     * Upper bound for each of the caches below.
     *
     * They are keyed by the line itself and static, so without a bound they retain a copy of every
     * distinct line of every document for the lifetime of the process — which for a long running
     * renderer means across projects. Documents are parsed line by line, so the entries that pay off
     * are the recent ones; dropping the whole table when it is full keeps the working set cached and
     * the retention bounded.
     */
    private const MAX_CACHE_ENTRIES = 5000;

    /** @var array<string, bool> Cache for isDirective results */
    private static array $directiveCache = [];

    /** @var array<string, bool> Cache for isLink results */
    private static array $linkCache = [];

    /** @var array<string, bool> Cache for isAnnotation results */
    private static array $annotationCache = [];

    private const HEADER_LETTERS = [
        '!',
        '"',
        '#',
        '$',
        '%',
        '&',
        '\'',
        '(',
        ')',
        '*',
        '+',
        ',',
        '-',
        '.',
        '/',
        ':',
        ';',
        '<',
        '=',
        '>',
        '?',
        '@',
        '[',
        '\\',
        ']',
        '^',
        '_',
        '`',
        '{',
        '|',
        '}',
        '~',
    ];

    public static function isSpecialLine(string $line, int $minimumLength = 2): string|null
    {
        if (mb_strlen($line) < $minimumLength) {
            return null;
        }

        $letter = $line[0];

        if (!in_array($letter, self::HEADER_LETTERS, true)) {
            return null;
        }

        $max = mb_strlen($line);
        for ($i = 1; $i < $max; $i++) {
            if ($line[$i] !== $letter) {
                return null;
            }
        }

        return $letter;
    }

    public static function isDirective(string $line): bool
    {
        if (isset(self::$directiveCache[$line])) {
            return self::$directiveCache[$line];
        }

        $result = preg_match('/^\.\.\s+(\|(.+)\| |)([^\s]+)::( (.*)|)$/mUsi', $line) > 0;
        if (count(self::$directiveCache) >= self::MAX_CACHE_ENTRIES) {
            self::$directiveCache = [];
        }

        self::$directiveCache[$line] = $result;

        return $result;
    }

    public static function isLink(string $line): bool
    {
        $trimmedLine = trim($line);
        if (isset(self::$linkCache[$trimmedLine])) {
            return self::$linkCache[$trimmedLine];
        }

        $result = preg_match('/^\.\.\s+_(.+):.*$/mUsi', $trimmedLine) > 0;
        if (count(self::$linkCache) >= self::MAX_CACHE_ENTRIES) {
            self::$linkCache = [];
        }

        self::$linkCache[$trimmedLine] = $result;

        return $result;
    }

    public static function isAnnotation(string $line): bool
    {
        if (isset(self::$annotationCache[$line])) {
            return self::$annotationCache[$line];
        }

        $result = preg_match('/^\.\.\s+\[([#a-zA-Z0-9]*)\]\s(.*)$$/mUsi', $line) > 0;
        if (count(self::$annotationCache) >= self::MAX_CACHE_ENTRIES) {
            self::$annotationCache = [];
        }

        self::$annotationCache[$line] = $result;

        return $result;
    }

    /**
     * RST explicit markup blocks (anchors, comments, directives, ...) start with two
     * dots followed by whitespace, or are a lonely `..`.
     *
     * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#explicit-markup-blocks
     */
    public static function isExplicitMarkup(string $line): bool
    {
        return preg_match('/^\.\.(\s.*|)$/mUsi', $line) > 0;
    }
}
