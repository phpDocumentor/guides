<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeRenderers;

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\DelegatingReferenceResolver;

/**
 * A decorator to make sure {@see ReferenceResolverNodeRenderer} is used for
 * inline link nodes.
 */
final class ReferenceResolvingNodeRendererFactory implements NodeRendererFactory
{
    public function __construct(private NodeRendererFactory $innerFactory, private DelegatingReferenceResolver $referenceResolver)
    {
    }

    public function get(Node $node): NodeRenderer
    {
        $renderer = $this->innerFactory->get($node);
        if ($node instanceof LinkInlineNode) {
            return new ReferenceResolverNodeRenderer($renderer, $this->referenceResolver);
        }

        return $renderer;
    }
}
