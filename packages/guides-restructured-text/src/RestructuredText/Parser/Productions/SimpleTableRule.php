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

use function count;
use function mb_substr;
use function preg_match;
use function sprintf;
use function strlen;
use function trim;

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
    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): Node|null
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
        $cellContents = [];
        $line = $documentIterator->current();
        foreach ($columnDefinitions as $column => $columnDefinition) {
            $cellContents[$column] = mb_substr($line, $columnDefinition['start'], $columnDefinition['length']);
            if ($columnDefinition['start'] + $columnDefinition['length'] >= strlen($line)) {
                continue;
            }

            // if length is null, it means this is the last column and there is no gap after
            if ($columnDefinition['length'] === null) {
                continue;
            }

            $gap = mb_substr($line, $columnDefinition['start'] + $columnDefinition['length'], 1);
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

        while (
            $documentIterator->getNextLine() !== null &&
            $this->startsWithBlankCell($documentIterator, $columnDefinitions[0])
        ) {
            $documentIterator->next();
            $line = $documentIterator->current();

            foreach ($columnDefinitions as $column => $columnDefinition) {
                $cellContents[$column] .= "\n" . mb_substr(
                    $line,
                    $columnDefinition['start'],
                    $columnDefinition['length'],
                );
            }
        }

        // We detected a colspan, we will have to redo the splitting according to the new column definition.
        if ($this->isColspanDefinition($documentIterator->getNextLine())) {
            $documentIterator->next();
        }

        $row = new TableRow();
        foreach ($cellContents as $content) {
            $row->addColumn($this->createColumn($content, $blockContext, 1));
        }

        return $row;
    }

    private function createColumn(
        string $content,
        BlockContext $blockContext,
        int $colspan,
    ): TableColumn {
        if (trim($content) === '\\') {
            $content = '';
        }

        $column = new TableColumn(trim($content), $colspan);
        $subContext = new BlockContext($blockContext->getDocumentParserContext(), $content, false, $blockContext->getDocumentIterator()->key());
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
