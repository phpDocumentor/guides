<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\ContainerNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * Divs a sub document in a div with a given class or set of classes.
 *
 * @link https://docutils.sourceforge.io/docs/ref/rst/directives.html#container
 */
class ContainerDirective extends SubDirective
{
    public function getName(): string
    {
        return 'container';
    }

    /** {@inheritDoc} */
    public function getAliases(): array
    {
        return ['div'];
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        return (new ContainerNode($collectionNode->getChildren()))->withOptions(['class' => $directive->getData()]);
    }
}
