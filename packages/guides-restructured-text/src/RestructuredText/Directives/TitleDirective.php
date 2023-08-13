<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * Add a meta title to the document
 *
 * .. title:: Page title
 */
class TitleDirective extends BaseDirective
{
    public function getName(): string
    {
        return 'title';
    }

    /** {@inheritDoc} */
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        $document = $blockContext->getDocumentParserContext()->getDocument();
        $document->setMetaTitle($directive->getData());

        return null;
    }
}
