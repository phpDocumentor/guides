<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\QuoteNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * An epigraph is an apposite (suitable, apt, or pertinent) short inscription,
 * often a quotation or poem, at the beginning of a document or section.
 * The "epigraph" directive produces an "epigraph"-class block quote.
 *
 * https://docutils.sourceforge.io/docs/ref/rst/directives.html#epigraph
 */
class EpigraphDirective extends SubDirective
{
    public function getName(): string
    {
        return 'epigraph';
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        return new QuoteNode($collectionNode->getChildren(), ['epigraph']);
    }
}
