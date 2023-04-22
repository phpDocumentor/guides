<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Metadata\MetaNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

/**
 * Add a meta information:
 *
 * .. meta::
 *      :key: value
 */
class Meta extends Directive
{
    public function getName(): string
    {
        return 'meta';
    }

    /** {@inheritDoc} */
    public function process(
        DocumentParserContext $documentParserContext,
        \phpDocumentor\Guides\RestructuredText\Parser\Directive $directive,
    ): Node|null {
        $document = $documentParserContext->getDocument();

        foreach ($directive->getOptions() as $option) {
            $document->addHeaderNode(new MetaNode($option->getName(), (string) $option->getValue()));
        }

        return null;
    }
}
