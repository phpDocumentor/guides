<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeRenderers;

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\DelegatingReferenceResolver;
use phpDocumentor\Guides\RenderContext;

/**
 * A decorator to resolve link URLs before rendering.
 */
final class ReferenceResolverNodeRenderer implements NodeRenderer
{
    public function __construct(private NodeRenderer $innerRenderer, private DelegatingReferenceResolver $referenceResolver)
    {
    }

    public function supports(Node $node): bool
    {
        return $this->innerRenderer->supports($node);
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        if ($node instanceof LinkInlineNode) {
            $this->referenceResolver->resolve($node, $renderContext);
        }

        return $this->innerRenderer->render($node, $renderContext);
    }
}
