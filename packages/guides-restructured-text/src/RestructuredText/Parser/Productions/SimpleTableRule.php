<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParser;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

final class SimpleTableRule implements Rule
{
    public function applies(DocumentParser $documentParser): bool
    {
        return preg_match('/^(?:[=]{2,}[ ]+)+[=]{2,}$/', trim($documentParser->getDocumentIterator()->current())) > 0;
    }

    public function apply(LinesIterator $documentIterator, ?Node $on = null): ?Node
    {
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
        $line = $documentIterator->current();
        $cellContents = [];
        foreach ($columnDefinitions as $column => $columnDefinition) {
            $cellContents[] = mb_substr($line, $columnDefinition['start'], $columnDefinition['length']);
        }
    }
}
