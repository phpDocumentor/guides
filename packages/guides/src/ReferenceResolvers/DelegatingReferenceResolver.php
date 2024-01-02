<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;

/**
 * Resolves the URL for all inline link nodes using reference resolvers.
 */
final class DelegatingReferenceResolver
{
    /** @param iterable<ReferenceResolver> $resolvers */
    public function __construct(private readonly iterable $resolvers)
    {
    }

    public function resolve(LinkInlineNode $node, RenderContext $renderContext, Messages $messages): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->resolve($node, $renderContext, $messages)) {
                return true;
            }
        }

        return false;
    }
}
