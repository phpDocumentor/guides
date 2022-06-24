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

                /*
                 * This part is broken, we want to parse all elements as fragment.
                 * - However in a case we get just a single paragraph node we want to have just the internal span.
                 * - the current enviroment is not passed. This makes it impossible to use links inside a table?
                 * - just lines are accepted right now, but according to spec all types of elements are allowed. including complex stuff like
                 *   code and other tables.
                 *
                 * This should somehow work, but we need to pass the current environment, which is in the parser instance.
                 * Maybe we should move the "isStart" for the lists into the environment so we are sure that it works as expected
                 * not based on the line iterator. As that will be at start once we are in a fragement?
                 *
                 * $node = $parser->parseFragment($col->getContent());
                 */
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
