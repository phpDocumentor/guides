<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use function in_array;
use function mb_strlen;

class LineChecker
{
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
}
