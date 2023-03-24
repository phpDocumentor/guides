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

use function is_callable;
use function is_string;

/** @implements NodeRenderer<Node> */
class DefaultNodeRenderer implements NodeRenderer, NodeRendererFactoryAware
{
    private ?NodeRendererFactory $nodeRendererFactory = null;

    public function setNodeRendererFactory(NodeRendererFactory $nodeRendererFactory): void
    {
        $this->nodeRendererFactory = $nodeRendererFactory;
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        $value = $node->getValue();

        if ($value instanceof Node) {
            assert($this->nodeRendererFactory !== null);
            return $this->nodeRendererFactory->get($value)->render($value, $renderContext);
        }

        if (is_string($value)) {
            return $value;
        }

        return '';
    }

    public function supports(Node $node): bool
    {
        return true;
    }
}
