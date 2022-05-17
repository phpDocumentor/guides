<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\Table;

use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\LineChecker;

abstract class AbstractTableBuilder implements TableBuilder
{
    public function buildNode(ParserContext $context, MarkupLanguageParser $parser, LineChecker $lineChecker): ?TableNode
    {
        $tableNode = $this->compile($context);

        if ($context->hasErrors()) {
            $tableAsString = $context->getTableAsString();
            foreach ($context->getErrors() as $error) {
                $parser->getEnvironment()
                    ->addError(sprintf("%s\nin file %s\n\n%s", $error, $parser->getEnvironment()->getCurrentFileName(), $tableAsString));
            }

            return null;
        }

        foreach ($tableNode->getData() as $row) {
            foreach ($row->getColumns() as $col) {
                $lines = explode("\n", $col->getContent());

                if ($lineChecker->isListLine($lines[0], false)) {
                    $node = $parser->parseFragment($col->getContent())->getNodes()[0];
                } else {
                    //TODO: fix this, as we need to parse table contents for links
                    $node = new SpanNode($col->getContent()); //SpanNode::create($parser, $col->getContent());
                }

                $col->setNode($node);
            }
        }

        return $tableNode;
    }

    abstract protected function compile(ParserContext $context): TableNode;
}
