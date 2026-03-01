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
use function array_map;
use function array_merge;
use function assert;
use function count;
use function explode;
use function implode;
use function is_string;
use function strval;
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
        $options = $this->optionsToArray($directive->getOptions());
        if ($directive->hasOption('file')) {
            $csvStream = $blockContext->getDocumentParserContext()
                ->getContext()
                ->getOrigin()
                ->readStream((string) $directive->getOption('file')->getValue());

            if ($csvStream === false) {
                $this->logger->error(
                    'Unable to read CSV file {file}',
                    array_merge(['file' => $directive->getOption('file')->getValue()], $blockContext->getLoggerInformation()),
                );

                return new GenericNode('csv-table');
            }

            $csv = Reader::from($csvStream);
        } else {
            $lines = $blockContext->getDocumentIterator()->toArray();
            $csv = Reader::fromString(implode("\n", $lines));
        }

        if ($directive->getOption('header-rows')->getValue() !== null) {
            $csv->setHeaderOffset((int) ($directive->getOption('header-rows')->getValue()) - 1);
        }

        $header = null;
        if ($directive->hasOption('header')) {
            $headerCsv = Reader::fromString($directive->getOption('header')->toString());

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
                assert(is_string($column) || $column === null);
                $columnNode = new TableColumn($column ?? '', 1, []);
                $tableRow->addColumn($this->buildColumn($columnNode, $blockContext, $this->productions));
            }

            $rows[] = $tableRow;
        }

        $tableNode = new TableNode($rows, array_filter([$header]));
        if (isset($options['widths']) && $options['widths'] !== 'auto' && $options['widths'] !== 'grid') {
            $colWidths = array_map('intval', explode(',', strval($options['widths'])));
            // A list of integers is used instead of the input column widths. Implies "grid".
            $options['widths'] = 'grid';
            $tableNode = $tableNode->withColumnWidth($colWidths);
        }

        $tableNode = $tableNode->withOptions($options);

        return $tableNode;
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
