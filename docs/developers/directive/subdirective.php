<?php

namespace YourExtension\Directives;

use phpDocumentor\Guides\RestructuredText\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\SubDirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\SubDirectiveParser;
use phpDocumentor\Guides\RestructuredText\Parser\SubDirectiveParserFactory;

class ExampleSubDirective extends SubDirective
{
    public function getName(): string
    {
        return 'example';
    }

    final protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        return new ExampleNode(
            $this->name,
            $directive->getDataNode(),
            $this->text,
            $collectionNode->getChildren(),
        );
    }
}
