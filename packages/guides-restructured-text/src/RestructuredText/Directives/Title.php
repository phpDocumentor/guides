<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Metadata\DocumentTitleNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

/**
 * Add a meta title to the document
 *
 * .. title:: Page title
 */
class Title extends Directive
{
    public function getName(): string
    {
        return 'title';
    }

    /** {@inheritDoc} */
    public function process(
        DocumentParserContext $documentParserContext,
        \phpDocumentor\Guides\RestructuredText\Parser\Directive $directive,
    ): Node|null {
        $document = $documentParserContext->getDocument();
        $document->addHeaderNode(new DocumentTitleNode($directive->getData()));

        return null;
    }
}
