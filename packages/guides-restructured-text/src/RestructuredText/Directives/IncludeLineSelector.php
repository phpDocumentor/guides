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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use InvalidArgumentException;

use function array_slice;
use function array_values;
use function count;
use function explode;
use function implode;
use function ksort;
use function max;
use function min;
use function preg_match;
use function preg_split;
use function str_contains;
use function trim;

final class IncludeLineSelector
{
    /** @see https://regex101.com/r/czvfnV/2 */
    public const LINE_NUMBER_RANGES_REGEX = '/^\d+(-\d*)?(?:,\s*\d+(-\d*)?)*$/';

    public function selectLines(string $contents, int $start, int|null $end = null): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $contents);
        if ($lines === false) {
            throw new InvalidArgumentException('Could not split included file into lines.');
        }

        return implode("\n", array_slice($lines, $start, max(0, ($end ?? count($lines)) - $start)));
    }

    /**
     * @param string[] $lines
     * @param callable(string): void|null $onOutOfRange
     *
     * @return string[]
     */
    public function selectLineRanges(array $lines, string $specification, callable|null $onOutOfRange = null): array
    {
        if (preg_match(self::LINE_NUMBER_RANGES_REGEX, $specification) !== 1) {
            throw new InvalidArgumentException('Invalid line range specification.');
        }

        $selected = [];
        foreach (explode(',', $specification) as $range) {
            $range = trim($range);
            [$first, $last] = $this->parseRange($range, count($lines));

            if ($first > $last) {
                if ($onOutOfRange !== null) {
                    $onOutOfRange($range);
                }

                continue;
            }

            for ($lineNumber = $first; $lineNumber <= $last; $lineNumber++) {
                $selected[$lineNumber] = $lines[$lineNumber - 1];
            }
        }

        ksort($selected);

        return array_values($selected);
    }

    /** @return array{int, int} */
    public function parseRange(string $range, int $lineCount): array
    {
        if (!str_contains($range, '-')) {
            $lineNumber = (int) $range;

            return [max(1, $lineNumber), min($lineNumber, $lineCount)];
        }

        [$first, $last] = explode('-', $range, 2);

        return [
            max(1, (int) $first),
            trim($last) === '' ? $lineCount : min((int) $last, $lineCount),
        ];
    }
}
