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
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;

/** @implements NodeRenderer<Node> */
final class PreRenderer implements NodeRenderer
{
    public function __construct(
        /** @var NodeRenderer<Node> */
        private readonly NodeRenderer $nodeRenderer,
        /** @var iterable<PreNodeRenderer> */
        private readonly iterable $preNodeRenderers,
    ) {
    }

    public function supports(string $nodeFqcn): bool
    {
        return $this->nodeRenderer->supports($nodeFqcn);
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        foreach ($this->preNodeRenderers as $preNodeRenderer) {
            $node = $preNodeRenderer->execute($node, $renderContext);
        }

        return $this->nodeRenderer->render($node, $renderContext);
    }
}
