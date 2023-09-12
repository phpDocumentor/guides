<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\QuoteNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * Highlights summarize the main points of a document or section, often consisting of a list.
 * The "highlights" directive produces a "highlights"-class block quote.
 *
 * https://docutils.sourceforge.io/docs/ref/rst/directives.html#highlights
 */
class HighlightsDirective extends SubDirective
{
    public function getName(): string
    {
        return 'highlights';
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        return new QuoteNode($collectionNode->getChildren(), ['highlights']);
    }
}
