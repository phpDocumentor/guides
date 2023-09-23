<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Metadata\MetaNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * Add a meta information:
 *
 * .. meta::
 *      :key: value
 */
class MetaDirective extends ActionDirective
{
    public function getName(): string
    {
        return 'meta';
    }

    public function processAction(BlockContext $blockContext, Directive $directive): void
    {
        $document = $blockContext->getDocumentParserContext()->getDocument();

        foreach ($directive->getOptions() as $option) {
            $document->addHeaderNode(new MetaNode($option->getName(), (string) $option->getValue()));
        }
    }
}
