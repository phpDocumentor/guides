<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\CollectionNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * Wraps a sub document in a div with a given class
 */
class Wrap extends SubDirective
{
    public function getName(): string
    {
        return 'wrap';
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        DocumentNode $document,
        Directive $directive,
    ): Node|null {
        return new CollectionNode($document->getChildren());
    }
}
