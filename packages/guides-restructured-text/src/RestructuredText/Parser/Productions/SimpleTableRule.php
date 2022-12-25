<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

final class SimpleTableRule implements Rule
{
    public function applies(DocumentParserContext $documentParser): bool
    {
        return preg_match('/^(?:[=]{2,}[ ]+)+[=]{2,}$/', trim($documentParser->getDocumentIterator()->current())) > 0;
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        $columnDefinition = $this->getColumnDefinition($documentIterator);
        $documentIterator->next();

        $this->tryParseRow($documentIterator, $columnDefinition);
    }

    private function getColumnDefinition(LinesIterator $documentIterator): array
    {
        $columnDefinition = [];
        $definitionLine = trim($documentIterator->current());

        $startPosition = 0;
        $lenght = 0;
        /*
         * In a simple table the first line defines the size of each column, the number of equals signs defines the
         * max column length. Except for the last column which is unbound
         */
        for ($i = 0; $i < strlen($definitionLine); $i++) {
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

    private function tryParseRow(LinesIterator $documentIterator, array $columnDefinitions)
    {
        /*
         * A row consists of columns, need to figure out how process cell contents, as it can be body elements
         * https://docutils.sourceforge.io/docs/ref/doctree.html#body-elements
         *
         * This basically means that we have to process the cell as some a fragement, but as we are parsing line by line
         * it's a bit harder. We need to detect rowspans and col spans, before going into the real parsing?
         */
        $line = $documentIterator->current();
        $cellContents = [];
        foreach ($columnDefinitions as $column => $columnDefinition) {
            $cellContents[] = mb_substr($line, $columnDefinition['start'], $columnDefinition['length']);
        }
    }
}
