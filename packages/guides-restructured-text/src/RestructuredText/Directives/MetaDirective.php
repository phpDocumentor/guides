<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Metadata\MetaNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * Add a meta information:
 *
 * .. meta::
 *      :key: value
 */
class MetaDirective extends BaseDirective
{
    public function getName(): string
    {
        return 'meta';
    }

    /** {@inheritDoc} */
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        $document = $blockContext->getDocumentParserContext()->getDocument();

        foreach ($directive->getOptions() as $option) {
            $document->addHeaderNode(new MetaNode($option->getName(), (string) $option->getValue()));
        }

        return null;
    }
}
