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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use Psr\Log\LoggerInterface;

use function array_filter;
use function array_map;
use function array_values;
use function count;
use function implode;
use function mb_substr;
use function preg_match;
use function preg_match_all;
use function sprintf;
use function strlen;
use function trim;

use const PREG_OFFSET_CAPTURE;

/** @implements Rule<TableNode> */
final class SimpleTableRule implements Rule
{
    public const PRIORITY = 40;

    public function __construct(
        private readonly RuleContainer $productions,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function applies(BlockContext $blockContext): bool
    {
        return $this->isColumnDefinitionLine($blockContext->getDocumentIterator()->current());
    }

    /** {@inheritDoc} */
    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): Node
    {
        $documentIterator = $blockContext->getDocumentIterator();
        $columnDefinition = $this->getColumnDefinition($documentIterator->current());
        $documentIterator->next();

        $headers = [];
        $rows = [];
        while ($documentIterator->valid()) {
            if (
                $this->isColumnDefinitionLine($documentIterator->current()) &&
                LinesIterator::isEmptyLine($documentIterator->getNextLine())
            ) {
                break;
            }

            if (
                LinesIterator::isNullOrEmptyLine($documentIterator->getNextLine()) === false &&
                $this->isColumnDefinitionLine($documentIterator->current())
            ) {
                $documentIterator->next();
                $headers = $rows;
                $rows = [];
            }

            if ($this->isColumnDefinitionLine($documentIterator->current()) === false) {
                $rows[] = $this->tryParseRow($blockContext, $columnDefinition);
            }

            $documentIterator->next();
        }

        return new TableNode($rows, $headers);
    }

    /** @return array<array-key, array{start: int, length:int|null}> */
    private function getColumnDefinition(string $line): array
    {
        $columnDefinition = [];
        $definitionLine = trim($line);

        $startPosition = 0;
        $lenght = 0;
        /*
         * In a simple table the first line defines the size of each column, the number of equals signs defines the
         * max column length. Except for the last column which is unbound
         */
        for ($i = 0, $iMax = strlen($definitionLine); $i < $iMax; $i++) {
            if ($definitionLine[$i] === ' ') {
                if ($lenght > 0) {
                    $columnDefinition[] = [
                        'start' => $startPosition,
                        'length' => $lenght,
                    ];

                    $startPosition += $lenght;
                }

                $lenght = 0;
                $startPosition++;
                continue;
            }

            if ($definitionLine[$i] !== '=') {
                return [];
            }

            $lenght++;
        }

        $columnDefinition[] = [
            'start' => $startPosition,
            'length' => null,
        ];

        return $columnDefinition;
    }

    /** @param array<array-key, array{start: int, length:int|null}> $columnDefinitions */
    private function tryParseRow(BlockContext $blockContext, array $columnDefinitions): TableRow
    {
        $documentIterator = $blockContext->getDocumentIterator();
        $lineOffset = $blockContext->getLineOffset($documentIterator->key());
        $lines = [$documentIterator->current()];
        while (
            $documentIterator->getNextLine() !== null &&
            $this->startsWithBlankCell($documentIterator, $columnDefinitions[0])
        ) {
            $documentIterator->next();
            $lines[] = $documentIterator->current();
        }

        // A row of dashes below a row says which of its cells span several columns
        $cells = array_values(array_map(
            static fn (array $columnDefinition): array => [...$columnDefinition, 'colspan' => 1],
            $columnDefinitions,
        ));
        if ($this->isColspanDefinition($documentIterator->getNextLine())) {
            $documentIterator->next();
            $cells = $this->applyColumnSpans($blockContext, $cells, $documentIterator->current());
        }

        $this->checkGaps($blockContext, $cells, $lines[0]);

        $row = new TableRow();
        foreach ($cells as $cell) {
            $content = [];
            foreach ($lines as $line) {
                $content[] = mb_substr($line, $cell['start'], $cell['length']);
            }

            $row->addColumn($this->createColumn(implode("\n", $content), $blockContext, $cell['colspan'], $lineOffset));
        }

        return $row;
    }

    /**
     * Merges the cells that a segment of the column span underline covers into one cell.
     *
     * @param list<array{start: int, length:int|null, colspan: int}> $cells
     *
     * @return list<array{start: int, length:int|null, colspan: int}>
     */
    private function applyColumnSpans(BlockContext $blockContext, array $cells, string $underline): array
    {
        preg_match_all('/-+/', $underline, $segments, PREG_OFFSET_CAPTURE);

        $spannedCells = [];
        $covered = 0;
        foreach ($segments[0] as [$dashes, $segmentStart]) {
            $segmentEnd = $segmentStart + strlen($dashes);
            $spanned = array_values(array_filter(
                $cells,
                static fn (array $cell): bool => $cell['start'] >= $segmentStart && $cell['start'] < $segmentEnd,
            ));
            if ($spanned === [] || $spanned[0]['start'] !== $segmentStart) {
                break;
            }

            $first = $spanned[0];
            $last = $spanned[count($spanned) - 1];
            $spannedCells[] = [
                'start' => $first['start'],
                'length' => $last['length'] === null ? null : $last['start'] + $last['length'] - $first['start'],
                'colspan' => count($spanned),
            ];
            $covered += count($spanned);
        }

        if ($covered === count($cells)) {
            return $spannedCells;
        }

        $this->logger->error(
            sprintf(
                'File "%s"; Malformed table: the column span underline "%s" does not line up with the columns',
                $blockContext->getDocumentParserContext()->getContext()->getCurrentFileName(),
                $underline,
            ),
            $blockContext->getLoggerInformation(),
        );

        return $cells;
    }

    /** @param list<array{start: int, length:int|null, colspan: int}> $cells */
    private function checkGaps(BlockContext $blockContext, array $cells, string $line): void
    {
        foreach ($cells as $cell) {
            // if length is null, it means this is the last column and there is no gap after
            if ($cell['length'] === null || $cell['start'] + $cell['length'] >= strlen($line)) {
                continue;
            }

            $gap = mb_substr($line, $cell['start'] + $cell['length'], 1);
            if ($gap === ' ') {
                continue;
            }

            $this->logger->error(
                sprintf(
                    'File "%s"; Malformed table: content "%s" appears in the "gap" on row "%s"',
                    $blockContext->getDocumentParserContext()->getContext()->getCurrentFileName(),
                    $gap,
                    $line,
                ),
                $blockContext->getLoggerInformation(),
            );
        }
    }

    private function createColumn(
        string $content,
        BlockContext $blockContext,
        int $colspan,
        int $lineOffset,
    ): TableColumn {
        if (trim($content) === '\\') {
            $content = '';
        }

        $column = new TableColumn(trim($content), $colspan);
        $subContext = new BlockContext($blockContext->getDocumentParserContext(), $content, false, $lineOffset);
        while ($subContext->getDocumentIterator()->valid()) {
            $this->productions->apply($subContext, $column);
        }

        $nodes = $column->getChildren();
        if (count($nodes) > 1) {
            return $column;
        }

        // the list item offset is determined by the offset of the first text
        $firstNode = $nodes[0] ?? null;
        if ($firstNode instanceof ParagraphNode) {
            return new TableColumn(trim($content), $colspan, $firstNode->getChildren());
        }

        return $column;
    }

    private function isColumnDefinitionLine(string $line): bool
    {
        return preg_match('/^(?:={2,} +)+={2,}$/', trim($line)) > 0;
    }

    private function isColspanDefinition(string|null $line): bool
    {
        if ($line === null) {
            return false;
        }

        return preg_match('/^(?:-{2,} +)+-{2,}$/', trim($line)) > 0;
    }

    /** @param array{start: int, length:int|null} $columnDefinition */
    private function startsWithBlankCell(LinesIterator $documentIterator, array $columnDefinition): bool
    {
        if ($documentIterator->getNextLine() === null) {
            return false;
        }

        $firstCellContent = mb_substr(
            $documentIterator->getNextLine(),
            $columnDefinition['start'],
            $columnDefinition['length'],
        );

        return trim($firstCellContent) === '';
    }
}
