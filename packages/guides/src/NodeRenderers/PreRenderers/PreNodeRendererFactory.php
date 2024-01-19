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
 */
final class PreNodeRendererFactory implements NodeRendererFactory
{
    public function __construct(
        private readonly NodeRendererFactory $innerFactory,
        /** @var iterable<PreNodeRenderer> */
        private readonly iterable $preRenderers,
    ) {
    }

    public function get(Node $node): NodeRenderer
    {
        $preRenderers = [];
        foreach ($this->preRenderers as $preRenderer) {
            if (!$preRenderer->supports($node)) {
                continue;
            }

            $preRenderers[] = $preRenderer;
        }

        if (count($preRenderers) === 0) {
            return $this->innerFactory->get($node);
        }

        return new PreRenderer($this->innerFactory->get($node), $preRenderers);
    }
}
