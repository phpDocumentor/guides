<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;
use Psr\Log\LoggerInterface;

use function sprintf;

/**
 * Resolves the URL for all inline link nodes using reference resolvers.
 */
final class DelegatingReferenceResolver
{
    /** @param iterable<ReferenceResolver> $resolvers */
    public function __construct(private readonly iterable $resolvers, private readonly LoggerInterface $logger)
    {
    }

    public function resolve(LinkInlineNode $node, RenderContext $renderContext): void
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->resolve($node, $renderContext)) {
                return;
            }
        }

        $this->logger->warning(
            sprintf(
                'Reference %s could not be resolved in %s',
                $node->getTargetReference(),
                $renderContext->getCurrentFileName(),
            ),
            $renderContext->getLoggerInformation(),
        );
    }
}
