<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use League\Csv\Reader;
use phpDocumentor\Guides\Nodes\GenericNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\RuleContainer;
use Psr\Log\LoggerInterface;

use function array_filter;
use function count;
use function implode;
use function trim;

/**
 * Render csv file as table
 *
 * .. csv-table:: Table Title
 *    :file: CSV file path and name
 *    :widths: 30, 70
 *    :header-rows: 1
 */
final class CsvTableDirective extends BaseDirective
{
    public function __construct(
        private RuleContainer $productions,
        private LoggerInterface $logger,
    ) {
    }

    public function getName(): string
    {
        return 'csv-table';
    }

    /** {@inheritDoc} */
    public function processNode(
        BlockContext $blockContext,
        Directive $directive,
    ): Node {
        if ($directive->hasOption('file')) {
            $csvStream = $blockContext->getDocumentParserContext()
                ->getContext()
                ->getOrigin()
                ->readStream((string) $directive->getOption('file')->getValue());

            if ($csvStream === false) {
                $this->logger->error('Unable to read CSV file {file}', ['file' => $directive->getOption('file')->getValue()]);

                return new GenericNode('csv-table');
            }

            $csv = Reader::createFromStream($csvStream);
        } else {
            $lines = $blockContext->getDocumentIterator()->toArray();
            $csv = Reader::createFromString(implode("\n", $lines));
        }

        if ($directive->getOption('header-rows')->getValue() !== null) {
            $csv->setHeaderOffset((int) ($directive->getOption('header-rows')->getValue()) - 1);
        }

        $header = null;
        if ($directive->hasOption('header')) {
            $headerCsv = Reader::createFromString($directive->getOption('header')->toString());
            $header = new TableRow();
            foreach ($headerCsv->first() as $column) {
                $columnNode = new TableColumn($column, 1, []);
                $header->addColumn($this->buildColumn($columnNode, $blockContext, $this->productions));
            }
        } elseif (empty($csv->getHeader()) === false) {
            $header = new TableRow();
            foreach ($csv->getHeader() as $column) {
                $columnNode = new TableColumn($column, 1, []);
                $header->addColumn($this->buildColumn($columnNode, $blockContext, $this->productions));
            }
        }

        $rows = [];
        foreach ($csv->getRecords() as $record) {
            $tableRow = new TableRow();
            foreach ($record as $column) {
                $columnNode = new TableColumn($column ?? '', 1, []);
                $tableRow->addColumn($this->buildColumn($columnNode, $blockContext, $this->productions));
            }

            $rows[] = $tableRow;
        }

        return new TableNode($rows, array_filter([$header]));
    }

    private function buildColumn(
        TableColumn $col,
        BlockContext $blockContext,
        RuleContainer $productions,
    ): TableColumn {
        $content = $col->getContent();
        $subContext = new BlockContext($blockContext->getDocumentParserContext(), $content, false, $blockContext->getDocumentIterator()->key());
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
