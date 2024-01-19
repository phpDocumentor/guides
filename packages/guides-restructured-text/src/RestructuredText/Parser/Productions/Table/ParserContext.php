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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\Table;

use function count;
use function implode;
use function ksort;

final class ParserContext
{
    /** @var string[] */
    private array $rawDataLines = [];
    /** @var array<int, TableSeparatorLineConfig> */
    private array $separatorLineConfigs = [];
    private int $currentLineNumber = 0;

    /** @var string[] */
    private array $errors = [];
    private int $headerRows = 0;

    public function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    public function pushContentLine(string $line): void
    {
        $this->rawDataLines[$this->currentLineNumber] = $line;
        $this->currentLineNumber++;
    }

    /** @return string[] */
    public function getDataLines(): array
    {
        return $this->rawDataLines;
    }

    public function pushSeparatorLine(TableSeparatorLineConfig $lineConfig): void
    {
        $this->separatorLineConfigs[$this->currentLineNumber] = $lineConfig;
        $this->currentLineNumber++;
    }

    public function getTableAsString(): string
    {
        $lines = [];
        $i = 0;
        while (isset($this->separatorLineConfigs[$i]) || isset($this->rawDataLines[$i])) {
            if (isset($this->separatorLineConfigs[$i])) {
                $lines[] = $this->separatorLineConfigs[$i]->getRawContent();
            } else {
                $lines[] = $this->rawDataLines[$i];
            }

            $i++;
        }

        return implode("\n", $lines);
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /** @return string[] */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getHeaderRows(): int
    {
        return $this->headerRows;
    }

    public function setHeaderRows(int $rowNumber): void
    {
        $this->headerRows = $rowNumber;
    }

    /** @return array<int, int> */
    public function getColumnRanges(): array
    {
        $columnRanges = [];

        foreach ($this->separatorLineConfigs as $separatorLine) {
            foreach ($separatorLine->getPartRanges() as [$colStart, $colEnd]) {
                // we don't have this "start" yet? just add it
                // in theory, should only happen for the first row
                if (!isset($columnRanges[$colStart])) {
                    $columnRanges[$colStart] = $colEnd;

                    continue;
                }

                // an exact column range we've already seen
                // OR, this new column goes beyond what we currently
                // have recorded, which means its a colspan, and so
                // we already have correctly recorded the "smallest"
                // current column ranges
                if ($columnRanges[$colStart] <= $colEnd) {
                    continue;
                }

                // this is not a new "start", but it is a new "end"
                // this means that we've found a "shorter" column that
                // we've seen before. We need to update the "end" of
                // the existing column, and add a "new" column
                $previousEnd = $columnRanges[$colStart];

                // A) update the end of this column to the new end
                $columnRanges[$colStart] = $colEnd;
                // B) add a new column from this new end, to the previous end
                $columnRanges[$colEnd + 1] = $previousEnd;
                ksort($columnRanges);
            }
        }

        return $columnRanges;
    }
}
