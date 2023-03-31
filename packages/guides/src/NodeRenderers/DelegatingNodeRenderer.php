<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeRenderers;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;

/** @implements NodeRenderer<Node> */
final class DelegatingNodeRenderer implements NodeRenderer, NodeRendererFactoryAware
{
    private NodeRendererFactory $nodeRendererFactory;

    public function setNodeRendererFactory(NodeRendererFactory $nodeRendererFactory): void
    {
        $this->nodeRendererFactory = $nodeRendererFactory;
    }

    public function supports(Node $node): bool
    {
        return true;
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        return $this->nodeRendererFactory->get($node)->render($node, $renderContext);
    }
}
