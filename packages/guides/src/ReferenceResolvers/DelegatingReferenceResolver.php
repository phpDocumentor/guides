<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;
use Psr\Log\LoggerInterface;

/**
 * Resolves the URL for all inline link nodes using reference resolvers.
 */
final class DelegatingReferenceResolver
{
    /** @param iterable<ReferenceResolver> $resolvers */
    public function __construct(private readonly iterable $resolvers, private LoggerInterface $logger)
    {
    }

    public function resolve(LinkInlineNode $node, RenderContext $renderContext): void
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->resolve($node, $renderContext)) {
                return;
            }
        }

        $this->logger->warning('Reference ' . $node->getTargetReference() . ' could not be resolved in ' . $renderContext->getCurrentFileName());
    }
}
