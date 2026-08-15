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

/** Decorator to add pre-rendering logic to node renderers. */
final class PreNodeRendererFactory implements NodeRendererFactory
{
    /** @var array<class-string<Node>, NodeRenderer<Node>> */
    private array $cache = [];

    private bool|null $supportsIsCachable = null;

    public function __construct(
        private readonly NodeRendererFactory $innerFactory,
        /** @var iterable<PreNodeRenderer> */
        private readonly iterable $preRenderers,
    ) {
    }

    public function get(Node $node): NodeRenderer
    {
        $cachable = $this->supportsIsCachable();
        $nodeFqcn = $node::class;

        if ($cachable && isset($this->cache[$nodeFqcn])) {
            return $this->cache[$nodeFqcn];
        }

        $preRenderers = [];
        foreach ($this->preRenderers as $preRenderer) {
            if (!$preRenderer->supports($node)) {
                continue;
            }

            $preRenderers[] = $preRenderer;
        }

        $renderer = count($preRenderers) === 0
            ? $this->innerFactory->get($node)
            : new PreRenderer($this->innerFactory->get($node), $preRenderers);

        if ($cachable) {
            $this->cache[$nodeFqcn] = $renderer;
        }

        return $renderer;
    }

    /**
     * Whether the set of pre-renderers can be decided once per node class.
     *
     * `PreNodeRenderer::supports()` takes a node instance and may inspect its state, so the answer can
     * differ between two nodes of one class — caching by class would then freeze whatever the first
     * node of that class happened to yield. Only when every pre-renderer declares, through
     * `PreNodeRendererCachableSupports`, that it looks at the class alone is the result reusable.
     */
    private function supportsIsCachable(): bool
    {
        if ($this->supportsIsCachable !== null) {
            return $this->supportsIsCachable;
        }

        $this->supportsIsCachable = true;
        foreach ($this->preRenderers as $preRenderer) {
            if ($preRenderer instanceof PreNodeRendererCachableSupports && $preRenderer->cacheSupport()) {
                continue;
            }

            $this->supportsIsCachable = false;
            break;
        }

        return $this->supportsIsCachable;
    }
}
