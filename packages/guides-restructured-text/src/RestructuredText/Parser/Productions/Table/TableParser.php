<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\Table;

use Exception;
use phpDocumentor\Guides\Nodes\TableNode;

use phpDocumentor\Guides\RestructuredText\Parser\Productions;
use function count;
use function in_array;
use function sprintf;
use function strlen;
use function trim;

class TableParser
{

    //We should have some simple value objects that represent valid combinations.
    //So we can simplify all kind of checks in this class.

    private const SIMPLE_TABLE_LETTER = '=';
    // "-" is valid as a separator in a simple table, except
    // on the first and last lines
    private const SIMPLE_TABLE_LETTER_ALT = '-';

    private const PRETTY_TABLE_LETTER = '-';

    private const PRETTY_TABLE_HEADER = '=';

    private const PRETTY_TABLE_JOINT = '+';

    /**
     * Parses a line from a table to see if it is a separator line.
     *
     * Returns TableSeparatorLineConfig if it *is* a separator, null otherwise.
     */
    public function parseTableSeparatorLine(string $line): ?TableSeparatorLineConfig
    {
        $line = trim($line);

        if ($line === '') {
            return null;
        }

        // Finds the table chars
        $chars = $this->findTableChars($line);

        if ($chars === null) {
            return null;
        }

        if ($chars[0] === self::PRETTY_TABLE_JOINT && $chars[1] === self::PRETTY_TABLE_LETTER) {
            // reverse the chars: - is the line char, + is the space char
            return $this->createSeparatorLineConfig($line, [self::PRETTY_TABLE_LETTER, self::PRETTY_TABLE_JOINT], false, true);
        }

        if ($chars[0] === self::PRETTY_TABLE_JOINT && $chars[1] === self::PRETTY_TABLE_HEADER) {
            // reverse the chars: = is the line char, + is the space char
            return $this->createSeparatorLineConfig($line, [self::PRETTY_TABLE_HEADER, self::PRETTY_TABLE_JOINT], true, true);
        }

        if ($chars[0] === self::SIMPLE_TABLE_LETTER && $chars[1] === ' ') {
            return $this->createSeparatorLineConfig($line, [self::SIMPLE_TABLE_LETTER, ' '], true, false);
        }

        if ($chars[0] === self::SIMPLE_TABLE_LETTER_ALT && $chars[1] === ' ') {
            return $this->createSeparatorLineConfig($line, [self::SIMPLE_TABLE_LETTER_ALT, ' '], false, false);
        }
    }

    public function guessTableType(string $line): string
    {
        return $line[0] === self::SIMPLE_TABLE_LETTER ? Productions\TableRule::TYPE_SIMPLE : Productions\TableRule::TYPE_PRETTY;
    }

    /**
     * A "line" separator always has only two characters.
     * This method returns those two characters.
     *
     * This returns null if this is not a separator line
     * or it's malformed in any way.
     *
     * @return string[]|null
     */
    private function findTableChars(string $line): ?array
    {
        $lineChar = $line[0];
        $spaceChar = null;

        $length = strlen($line);
        for ($i = 0; $i < $length; $i++) {
            if ($line[$i] === $lineChar) {
                continue;
            }

            if ($spaceChar === null) {
                $spaceChar = $line[$i];

                continue;
            }

            if ($line[$i] !== $spaceChar) {
                return null;
            }
        }

        if ($spaceChar === null) {
            return null;
        }

        return [$lineChar, $spaceChar];
    }

    private function createSeparatorLineConfig(string $line, array $chars, bool $header, bool $pretty): ?\phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\TableSeparatorLineConfig
    {
        //Chars[0] is the line char
        //chars[1] is the column marker in the table line
        $parts = [];
        $strlen = strlen($line);

        //If we are handling a pretty table, the first char is a +
        $currentPartStart = $i = $pretty ? 1 : 0;
        $i++;

        for ($i; $i < $strlen; $i++) {
            //If our previous char was also a column marker there is something wrong.
            if (($line[$i - 1] ?? null) === $chars[1]) {
                throw new Exception(sprintf('Unexpected char "%s"', $line[$i]));
            }

            if ($line[$i] === $chars[1]) {
                $parts[] = [$currentPartStart, $i];
                $currentPartStart = ++$i;
                continue;
            }

            //Only allow line chars
            if ($line[$i] !== $chars[0]) {
                throw new Exception(sprintf('Unexpected char "%s"', $line[$i]));
            }
        }

        // finish the last "part" for non pretty tables.
        if ($pretty === false) {
            $parts[] = [$currentPartStart, $i];
        }

        if (count($parts) <= 1) {
            return null;
        }

        return new TableSeparatorLineConfig(
            $header,
            $pretty ? Productions\TableRule::TYPE_PRETTY : Productions\TableRule::TYPE_SIMPLE,
            $parts,
            $chars[0],
            $line
        );
    }
}
