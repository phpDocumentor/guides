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
use Psr\Log\LoggerInterface;

use function assert;
use function is_array;
use function is_string;
use function sprintf;

/** @implements NodeRenderer<Node> */
final class DefaultNodeRenderer implements NodeRenderer, NodeRendererFactoryAware
{
    private NodeRendererFactory|null $nodeRendererFactory = null;

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

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

        if (is_array($value)) {
            $returnValue = '';
            foreach ($value as $child) {
                if ($child instanceof Node) {
                    $returnValue .= $this->render($child, $renderContext);
                } else {
                    $this->logger->error(
                        sprintf('The default renderer cannot be applied to node %s', $node::class),
                        $renderContext->getLoggerInformation(),
                    );
                }
            }

            return $returnValue;
        }

        if (is_string($value)) {
            return $value;
        }

        $this->logger->error(
            sprintf('The default renderer cannot be applied to node %s', $node::class),
            $renderContext->getLoggerInformation(),
        );

        return '';
    }

    public function supports(string $nodeFqcn): bool
    {
        return true;
    }
}
