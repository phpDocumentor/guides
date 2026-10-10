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

use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\RuleContainer;
use Psr\Log\LoggerInterface;

use function array_fill;
use function array_flip;
use function array_keys;
use function array_map;
use function array_shift;
use function array_slice;
use function count;
use function explode;
use function implode;
use function ksort;
use function ltrim;
use function mb_str_split;
use function min;
use function preg_match;
use function rtrim;
use function sprintf;
use function strlen;
use function substr;
use function trim;
use function usort;

use const PHP_INT_MAX;

final class GridTableBuilder
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    /**
     * Finds the cells of the table as rectangles of borders, the way docutils does: each cell starts at a "+" corner
     * and ends at the first "+" to the right, below and on the way back that close a rectangle. The row and column of
     * a cell, and how many rows and columns it spans, follow from where its borders lie among the borders of all cells.
     */
    protected function compile(ParserContext $context): TableNode
    {
        $grid = array_map(
            static fn (string $line): array => mb_str_split($line),
            explode("\n", $context->getTableAsString()),
        );
        $headerSeparator = null;
        foreach ($grid as $lineIndex => $line) {
            if (preg_match('/^\+(?:=+\+)+$/', implode('', $line)) !== 1) {
                continue;
            }

            $headerSeparator = $lineIndex;
            $grid[$lineIndex] = array_map(static fn (string $char): string => $char === '=' ? '-' : $char, $line);
        }

        $cells = $this->scanCells($grid, $context);
        if ($cells === []) {
            return new TableNode([]);
        }

        $rowSeparators = $this->separators($cells, 0, 2);
        $columnSeparators = $this->separators($cells, 1, 3);

        /** @var array<int, list<array{int, int, int, int}>> $cellsByRow */
        $cellsByRow = [];
        foreach ($cells as $cell) {
            $cellsByRow[$rowSeparators[$cell[0]]][] = $cell;
        }

        ksort($cellsByRow);
        $headers = [];
        $rows = [];
        foreach ($cellsByRow as $rowCells) {
            usort($rowCells, static fn (array $a, array $b): int => $a[1] <=> $b[1]);
            $row = new TableRow();
            foreach ($rowCells as [$top, $left, $bottom, $right]) {
                $row->addColumn(new TableColumn(
                    $this->cellContent($grid, $top, $left, $bottom, $right),
                    $columnSeparators[$right] - $columnSeparators[$left],
                    [],
                    $rowSeparators[$bottom] - $rowSeparators[$top],
                ));
            }

            if ($headerSeparator !== null && $rowCells[0][0] < $headerSeparator) {
                $headers[] = $row;
            } else {
                $rows[] = $row;
            }
        }

        return new TableNode($rows, $headers);
    }

    /**
     * @param list<list<string>> $grid
     *
     * @return list<array{int, int, int, int}> the top, left, bottom and right border of each cell
     */
    private function scanCells(array $grid, ParserContext $context): array
    {
        $bottom = count($grid) - 1;
        $right = count($grid[0]) - 1;
        // the last line of the grid that is covered by cells, for each column of characters
        $done = array_fill(0, $right, -1);
        $cells = [];
        /** @var list<array{int, int}> $corners the corners that a cell may start at, top to bottom */
        $corners = [[0, 0]];
        while ($corners !== []) {
            [$top, $left] = array_shift($corners);
            if ($top === $bottom || $left >= $right || $top <= $done[$left]) {
                continue;
            }

            $cell = $this->scanCell($grid, $top, $left);
            if ($cell === null) {
                continue;
            }

            [$cellBottom, $cellRight] = $cell;
            for ($column = $left; $column < $cellRight && $column < $right; $column++) {
                $done[$column] = $cellBottom - 1;
            }

            $cells[] = [$top, $left, $cellBottom, $cellRight];
            $corners[] = [$top, $cellRight];
            $corners[] = [$cellBottom, $left];
            usort($corners, static fn (array $a, array $b): int => $a <=> $b);
        }

        foreach ($done as $lastLine) {
            if ($lastLine !== $bottom - 1) {
                $context->addError('Malformed table: the borders of the cells do not form a complete table');

                return [];
            }
        }

        return $cells;
    }

    /**
     * @param list<list<string>> $grid
     *
     * @return array{int, int}|null the bottom and right border of the cell starting at this corner
     */
    private function scanCell(array $grid, int $top, int $left): array|null
    {
        $width = count($grid[$top]);
        for ($right = $left + 1; $right < $width; $right++) {
            $char = $grid[$top][$right];
            // Unlike docutils, accept a cell border that starts below a "-" instead of a "+", as tables written for
            // the earlier parser of this library may leave out the "+" under a cell that spans several columns
            if ($char === '+' || ($char === '-' && ($grid[$top + 1][$right] ?? '') === '|')) {
                $bottom = $this->scanDown($grid, $top, $left, $right);
                if ($bottom !== null) {
                    return [$bottom, $right];
                }
            } elseif ($char !== '-') {
                return null;
            }
        }

        return null;
    }

    /** @param list<list<string>> $grid */
    private function scanDown(array $grid, int $top, int $left, int $right): int|null
    {
        for ($bottom = $top + 1, $lines = count($grid); $bottom < $lines; $bottom++) {
            $char = $grid[$bottom][$right] ?? '';
            if ($char === '+') {
                if ($this->isBottomBorder($grid, $top, $left, $bottom, $right)) {
                    return $bottom;
                }
            } elseif ($char !== '|') {
                return null;
            }
        }

        return null;
    }

    /** @param list<list<string>> $grid */
    private function isBottomBorder(array $grid, int $top, int $left, int $bottom, int $right): bool
    {
        for ($column = $right - 1; $column > $left; $column--) {
            $char = $grid[$bottom][$column] ?? '';
            if ($char !== '+' && $char !== '-') {
                return false;
            }
        }

        if (($grid[$bottom][$left] ?? '') !== '+') {
            return false;
        }

        for ($line = $bottom - 1; $line > $top; $line--) {
            $char = $grid[$line][$left] ?? '';
            if ($char !== '+' && $char !== '|') {
                return false;
            }
        }

        return true;
    }

    /**
     * Numbers the borders that cells start or end at, in the order they appear in.
     *
     * @param list<array{int, int, int, int}> $cells
     *
     * @return array<int, int> the number of each border, by its position
     */
    private function separators(array $cells, int $start, int $end): array
    {
        $positions = [];
        foreach ($cells as $cell) {
            $positions[$cell[$start]] = true;
            $positions[$cell[$end]] = true;
        }

        ksort($positions);

        return array_flip(array_keys($positions));
    }

    /** @param list<list<string>> $grid */
    private function cellContent(array $grid, int $top, int $left, int $bottom, int $right): string
    {
        $lines = [];
        $indentation = null;
        for ($line = $top + 1; $line < $bottom; $line++) {
            $text = rtrim(implode('', array_slice($grid[$line], $left + 1, $right - $left - 1)));
            $lines[] = $text;
            if ($text === '') {
                continue;
            }

            $indentation = min($indentation ?? PHP_INT_MAX, strlen($text) - strlen(ltrim($text, ' ')));
        }

        // Remove the indentation all lines share, so the content keeps its own indentation, like that of a list
        return trim(implode("\n", array_map(
            static fn (string $text): string => substr($text, $indentation ?? 0),
            $lines,
        )));
    }

    public function buildNode(
        ParserContext $tableParserContext,
        BlockContext $blockContext,
        RuleContainer $productions,
    ): TableNode|null {
        $tableNode = $this->compile($tableParserContext);

        if ($tableParserContext->hasErrors()) {
            $tableAsString = $tableParserContext->getTableAsString();
            foreach ($tableParserContext->getErrors() as $error) {
                $message = sprintf(
                    "%s\nin file %s\n\n%s",
                    $error,
                    $blockContext->getDocumentParserContext()->getContext()->getCurrentFileName(),
                    $tableAsString,
                );
                $this->logger->error($message, $blockContext->getLoggerInformation());
            }

            return null;
        }

        $headers = [];
        foreach ($tableNode->getHeaders() as $row) {
            $headers[] = $this->buildRow($row, $blockContext, $productions, $tableParserContext->getLineOffset());
        }

        $rows = [];
        foreach ($tableNode->getData() as $row) {
            $rows[] = $this->buildRow($row, $blockContext, $productions, $tableParserContext->getLineOffset());
        }

        return new TableNode($rows, $headers);
    }

    private function buildRow(
        TableRow $row,
        BlockContext $blockContext,
        RuleContainer $productions,
        int $lineOffset,
    ): TableRow {
        $newRow = new TableRow();
        foreach ($row->getColumns() as $col) {
            $newRow->addColumn($this->buildColumn($col, $blockContext, $productions, $lineOffset));
        }

        return $newRow;
    }

    private function buildColumn(
        TableColumn $col,
        BlockContext $blockContext,
        RuleContainer $productions,
        int $lineOffset,
    ): TableColumn {
        $content = $col->getContent();
        // Rows don't keep the lines they were parsed from, so cell content is located at the table's first line
        $subContext = new BlockContext($blockContext->getDocumentParserContext(), $content, false, $lineOffset);
        while ($subContext->getDocumentIterator()->valid()) {
            $productions->apply($subContext, $col);
        }

        $nodes = $col->getChildren();
        if (count($nodes) > 1) {
            return $col;
        }

        // the list item offset is determined by the offset of the first text
        $firstNode = $nodes[0] ?? null;
        if ($firstNode instanceof ParagraphNode) {
            return new TableColumn(trim($content), $col->getColSpan(), $firstNode->getChildren(), $col->getRowSpan());
        }

        return $col;
    }
}
