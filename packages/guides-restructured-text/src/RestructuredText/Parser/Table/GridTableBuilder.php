<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Table;

use Exception;
use LogicException;
use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\Exception\InvalidTableStructure;

class GridTableBuilder extends AbstractTableBuilder
{
    protected function compile(ParserContext $context): TableNode
    {
        // loop over ALL separator lines to find ALL of the column ranges
        /** @var array<int, int> $columnRanges */
        $columnRanges = [];
        $finalHeadersRow = 0;
        foreach ($context->getLineSeparators() as $rowIndex => $separatorLine) {
            if ($separatorLine->isHeader()) {
                if ($finalHeadersRow !== 0) {
                    $context->addError(
                        sprintf(
                            'Malformed table: multiple "header rows" using "===" were found. See table '
                            . 'lines "%d" and "%d"',
                            $finalHeadersRow + 1,
                            $rowIndex
                        )
                    );
                }

                // indicates that "=" was used
                $finalHeadersRow = $rowIndex - 1;
            }

            foreach ($separatorLine->getPartRanges() as $columnRange) {
                $colStart = $columnRange[0];
                $colEnd = $columnRange[1];

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

        /** @var TableRow[] $rows */
        $rows = [];
        $partialSeparatorRows = [];
        foreach ($context->getDataLines() as $rowIndex => $line) {
            $row = new TableRow();

            // if the row is part separator row, part content, this
            // is a rowspan situation - e.g.
            // |           +----------------+----------------------------+
            // look for +-----+ pattern
            if (preg_match('/\+[-]+\+/', $line) === 1) {
                $partialSeparatorRows[$rowIndex] = true;
            }

            $currentColumnStart = null;
            $currentSpan = 1;
            /** @var ?int $previousColumnEnd */
            $previousColumnEnd = null;
            foreach ($columnRanges as $start => $end) {
                // a content line that ends before it should
                if ($end >= mb_strlen($line)) {
                    $context->addError(sprintf(
                        "Malformed table: Line\n\n%s\n\ndoes not appear to be a complete table row",
                        $line
                    ));

                    break;
                }

                if ($currentColumnStart !== null) {
                    if ($previousColumnEnd === null) {
                        throw new LogicException('The previous column end is not set yet');
                    }

                    $gapText = mb_substr($line, $previousColumnEnd, $start - $previousColumnEnd);
                    if (mb_strpos($gapText, '|') === false && mb_strpos($gapText, '+') === false) {
                        // text continued through the "gap". This is a colspan
                        // "+" is an odd character - it's usually "|", but "+" can
                        // happen in row-span situations
                        $currentSpan++;
                    } else {
                        // we just hit a proper "gap" record the line up until now
                        $row->addColumn(
                            new TableColumn(
                                mb_substr($line, $currentColumnStart, $previousColumnEnd - $currentColumnStart),
                                $currentSpan
                            )
                        );
                        $currentSpan = 1;
                        $currentColumnStart = null;
                    }
                }

                // if the current column start is null, then set it
                // other wise, leave it - this is a colspan, and eventually
                // we want to get all the text starting here
                if ($currentColumnStart === null) {
                    $currentColumnStart = $start;
                }

                $previousColumnEnd = $end;
            }

            // record the last column
            if ($currentColumnStart !== null) {
                if ($previousColumnEnd === null) {
                    throw new LogicException('The previous column end is not set yet');
                }

                $row->addColumn(
                    new TableColumn(
                        mb_substr($line, $currentColumnStart, $previousColumnEnd - $currentColumnStart),
                        $currentSpan
                    )
                );
            }

            $rows[$rowIndex] = $row;
        }

        $columnIndexesCurrentlyInRowspan = [];
        foreach ($rows as $rowIndex => $row) {
            if (isset($partialSeparatorRows[$rowIndex])) {
                // this row is part content, part separator due to a rowspan
                // for each column that contains content, we need to
                // push it onto the last real row's content and record
                // that this column in the next row should also be
                // included in that previous row's content
                foreach ($row->getColumns() as $columnIndex => $column) {
                    if (!$column->isCompletelyEmpty()
                        && str_repeat(
                            '-',
                            mb_strlen($column->getContent())
                        ) === $column->getContent()
                    ) {
                        // only a line separator in this column - not content!
                        continue;
                    }

                    $prevTargetColumn = $this->findColumnInPreviousRows((int) $columnIndex, $rows, (int) $rowIndex);
                    $prevTargetColumn->addContent("\n" . $column->getContent());
                    $prevTargetColumn->incrementRowSpan();
                    // mark that this column on the next row should also be added
                    // to the previous row
                    $columnIndexesCurrentlyInRowspan[] = $columnIndex;
                }

                // remove the row - it's not real
                unset($rows[$rowIndex]);

                continue;
            }

            // check if the previous row was a partial separator row, and
            // we need to take some columns and add them to a previous row's content
            foreach ($columnIndexesCurrentlyInRowspan as $columnIndex) {
                $prevTargetColumn = $this->findColumnInPreviousRows($columnIndex, $rows, (int) $rowIndex);
                $columnInRowspan = $row->getColumn($columnIndex);
                if ($columnInRowspan === null) {
                    throw new LogicException('Cannot find column for index "%s"', $columnIndex);
                }

                $prevTargetColumn->addContent("\n" . $columnInRowspan->getContent());

                // now this column actually needs to be removed from this row,
                // as it's not a real column that needs to be printed
                $row->removeColumn($columnIndex);
            }

            $columnIndexesCurrentlyInRowspan = [];

            // if the next row is just $i+1, it means there
            // was no "separator" and this is really just a
            // continuation of this row.
            $nextRowCounter = 1;
            while (isset($rows[(int) $rowIndex + $nextRowCounter])) {
                // but if the next line is actually a partial separator, then
                // it is not a continuation of the content - quit now
                if (isset($partialSeparatorRows[(int) $rowIndex + $nextRowCounter])) {
                    break;
                }

                $targetRow = $rows[(int) $rowIndex + $nextRowCounter];
                unset($rows[(int) $rowIndex + $nextRowCounter]);

                try {
                    $row->absorbRowContent($targetRow);
                } catch (InvalidTableStructure $e) {
                    $context->addError($e->getMessage());
                }

                $nextRowCounter++;
            }
        }

        $headers = [];
        // one more loop to set headers
        foreach ($rows as $rowIndex => $_row) {
            if ($rowIndex > $finalHeadersRow) {
                continue;
            }

            $headers[$rowIndex] = true;
        }

        return new TableNode($rows, $headers);
    }



    /**
     * @param TableRow[] $rows
     */
    private function findColumnInPreviousRows(int $columnIndex, array $rows, int $currentRowIndex): TableColumn
    {
        /** @var TableRow[] $reversedRows */
        $reversedRows = array_reverse($rows, true);

        // go through the rows backwards to find the last/previous
        // row that actually had a real column at this position
        foreach ($reversedRows as $k => $row) {
            // start by skipping any future rows, as we go backward
            if ($k >= $currentRowIndex) {
                continue;
            }

            $prevTargetColumn = $row->getColumn($columnIndex);
            if ($prevTargetColumn !== null) {
                return $prevTargetColumn;
            }
        }

        throw new Exception('Could not find column in any previous rows');
    }
}
