<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\NodeRenderers;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;

/** @implements NodeRenderer<Node> */
final class DelegatingNodeRenderer implements NodeRenderer, NodeRendererFactoryAware
{
    private NodeRendererFactory $nodeRendererFactory;

    public function setNodeRendererFactory(NodeRendererFactory $nodeRendererFactory): void
    {
        if (isset($this->nodeRendererFactory)) {
            return;
        }

        $this->nodeRendererFactory = $nodeRendererFactory;
    }

    public function supports(string $nodeFqcn): bool
    {
        return true;
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        return $this->nodeRendererFactory->get($node)->render($node, $renderContext);
    }
}
