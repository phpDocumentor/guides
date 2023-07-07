<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\NodeRenderers\PreRenderers\PreNodeRenderer;
use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use Webmozart\Assert\Assert;

final class ReferenceResolverPreRender implements PreNodeRenderer
{
    public function __construct(private readonly DelegatingReferenceResolver $referenceResolver)
    {
    }

    public function supports(Node $node): bool
    {
        return $node instanceof LinkInlineNode;
    }

    public function execute(Node $node, RenderContext $renderContext): Node
    {
        Assert::isInstanceOf($node, LinkInlineNode::class);
        $this->referenceResolver->resolve($node, $renderContext);

        return $node;
    }
}
