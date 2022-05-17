<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Table;

use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\Exception\InvalidTableStructure;

class SimpleTableBuilder extends AbstractTableBuilder
{
    protected function compile(ParserContext $context): TableNode
    {
        $rows = [];
        $headers = [];

        // determine if there is second === separator line (other than
        // the last line): this would mean there are header rows
        $finalHeadersRow = 0;
        foreach ($context->getLineSeparators() as $i => $separatorLine) {
            // skip the first line: we're looking for the *next* line
            if ($i === 0) {
                continue;
            }

            // we found the next ==== line
            if ($separatorLine->getLineCharacter() === '=') {
                // found the end of the header rows
                $finalHeadersRow = $i;

                break;
            }
        }

        // if the final header row is *after* the last data line, it's not
        // really a header "ending" and so there are no headers
        $lastDataLineNumber = array_keys($context->getDataLines())[count($context->getDataLines()) - 1];
        if ($finalHeadersRow > $lastDataLineNumber) {
            $finalHeadersRow = 0;
        }

        // todo - support "---" in the future for colspan
        $columnRanges = $context->getLineSeparators()[0]->getPartRanges();
        $lastColumnRangeEnd = array_values($columnRanges)[count($columnRanges) - 1][1];
        foreach ($context->getDataLines() as $i => $line) {
            $row = new TableRow();
            // loop over where all the columns should be

            $previousColumnEnd = null;
            foreach ($columnRanges as $columnRange) {
                $isRangeBeyondText = $columnRange[0] >= mb_strlen($line);
                // check for content in the "gap"
                if ($previousColumnEnd !== null && !$isRangeBeyondText) {
                    $gapText = mb_substr($line, $previousColumnEnd, $columnRange[0] - $previousColumnEnd);
                    if (mb_strlen(trim($gapText)) !== 0) {
                        $context->addError(
                            sprintf('Malformed table: content "%s" appears in the "gap" on row "%s"', $gapText, $line)
                        );
                    }
                }

                if ($isRangeBeyondText) {
                    // the text for this line ended earlier. This column should be blank

                    $content = '';
                } elseif ($lastColumnRangeEnd === $columnRange[1]) {
                    // this is the last column, so get the rest of the line
                    // this is because content can go *beyond* the table legally
                    $content = mb_substr(
                        $line,
                        $columnRange[0]
                    );
                } else {
                    $content = mb_substr(
                        $line,
                        $columnRange[0],
                        $columnRange[1] - $columnRange[0]
                    );
                }

                $content = trim($content);
                $row->addColumn(new TableColumn($content, 1));

                $previousColumnEnd = $columnRange[1];
            }

            // is header row?
            if ($i <= $finalHeadersRow) {
                $headers[$i] = true;
            }

            $rows[$i] = $row;
        }

        /** @var TableRow|null $previousRow */
        $previousRow = null;
        // check for empty first columns, which means this is
        // not a new row, but the continuation of the previous row
        foreach ($rows as $i => $row) {
            if ($row->getFirstColumn()->isCompletelyEmpty() && $previousRow !== null) {
                try {
                    $previousRow->absorbRowContent($row);
                } catch (InvalidTableStructure $e) {
                    $context->addError($e->getMessage());
                }

                unset($rows[$i]);

                continue;
            }

            $previousRow = $row;
        }

        return new TableNode($rows, $headers);
    }
}
