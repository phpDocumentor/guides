<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\DocumentBlockNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

class DocumentBlockDirective extends SubDirective
{
    public function getName(): string
    {
        return 'documentblock';
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $identifier = ((string) $directive->getOption('identifier')->getValue());

        return new DocumentBlockNode(
            $collectionNode->getChildren(),
            $identifier,
        );
    }
}
