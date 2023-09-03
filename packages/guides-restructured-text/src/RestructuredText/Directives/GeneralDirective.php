<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * A catch-all directive, the content is treated as content, the options passed on
 */
class GeneralDirective extends SubDirective
{
    public function getName(): string
    {
        return '';
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        return new GeneralDirectiveNode(
            $directive->getName(),
            $directive->getData(),
            $directive->getDataNode() ?? new InlineCompoundNode(),
            $collectionNode->getChildren(),
        );
    }
}
