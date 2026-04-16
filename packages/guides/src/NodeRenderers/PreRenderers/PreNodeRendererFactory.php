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

namespace phpDocumentor\Guides\NodeRenderers\PreRenderers;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\Nodes\Node;

use function count;

/**
 * Decorator to add pre-rendering logic to node renderers.
 *
 * Note: Caching assumes PreNodeRenderer::supports() only checks the node's
 * class type, not instance-specific properties. If a PreNodeRenderer needs
 * to check node properties, caching by class would return incorrect results.
 */
final class PreNodeRendererFactory implements NodeRendererFactory
{
    /** @var array<class-string<Node>, NodeRenderer<Node>> */
    private array $cache = [];

    public function __construct(
        private readonly NodeRendererFactory $innerFactory,
        /** @var iterable<PreNodeRenderer> */
        private readonly iterable $preRenderers,
    ) {
    }

    public function get(Node $node): NodeRenderer
    {
        // Cache by node class to avoid repeated preRenderer iteration
        $nodeFqcn = $node::class;
        if (isset($this->cache[$nodeFqcn])) {
            return $this->cache[$nodeFqcn];
        }

        $preRenderers = [];
        foreach ($this->preRenderers as $preRenderer) {
            if (!$preRenderer->supports($node)) {
                continue;
            }

            $preRenderers[] = $preRenderer;
        }

        if (count($preRenderers) === 0) {
            return $this->cache[$nodeFqcn] = $this->innerFactory->get($node);
        }

        return $this->cache[$nodeFqcn] = new PreRenderer($this->innerFactory->get($node), $preRenderers);
    }
}
