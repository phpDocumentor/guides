<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use phpDocumentor\Guides\Nodes\Lists\ListItem;

use function in_array;
use function mb_strlen;
use function preg_match;
use function strlen;
use function trim;

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

    public static function isSpecialLine(string $line, int $minimumLength = 2): ?string
    {
        if (mb_strlen($line) < $minimumLength) {
            return null;
        }

        $letter = $line[0];

        if (!in_array($letter, self::HEADER_LETTERS, true)) {
            return null;
        }

        for ($i = 1; $i < mb_strlen($line); $i++) {
            if ($line[$i] !== $letter) {
                return null;
            }
        }

        return $letter;
    }

    public function isListLine(string $line, bool $isCode): bool
    {
        $listLine = $this->parseListLine($line);

        if ($listLine !== null) {
            return $listLine->getDepth() === 0 || !$isCode;
        }

        return false;
    }

    private function parseListLine(string $line): ?ListItem
    {
        $depth = 0;

        for ($i = 0; $i < strlen($line); $i++) {
            $char = $line[$i];

            if ($char === ' ') {
                $depth++;
            } elseif ($char === "\t") {
                $depth += 2;
            } else {
                break;
            }
        }

        if (preg_match('/^((\*|\-)|([\d#]+)\.) (.+)$/', trim($line), $match) > 0) {
            return new ListItem(
                $line[$i],
                $line[$i] !== '*' && $line[$i] !== '-',
                $depth,
                [$match[4]],
            );
        }

        if (strlen($line) === 1 && $line[0] === '-') {
            return new ListItem(
                $line[$i],
                $line[$i] !== '*' && $line[$i] !== '-',
                $depth,
                [''],
            );
        }

        return null;
    }
}
