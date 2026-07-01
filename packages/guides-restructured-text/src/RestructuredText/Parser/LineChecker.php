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

use function in_array;
use function mb_strlen;
use function preg_match;
use function trim;

final class LineChecker
{
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
        self::$linkCache[$trimmedLine] = $result;

        return $result;
    }

    public static function isAnnotation(string $line): bool
    {
        if (isset(self::$annotationCache[$line])) {
            return self::$annotationCache[$line];
        }

        $result = preg_match('/^\.\.\s+\[([#a-zA-Z0-9]*)\]\s(.*)$$/mUsi', $line) > 0;
        self::$annotationCache[$line] = $result;

        return $result;
    }
}
