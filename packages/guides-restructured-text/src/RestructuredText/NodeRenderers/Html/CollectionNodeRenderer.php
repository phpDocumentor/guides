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

namespace phpDocumentor\Guides\RestructuredText\NodeRenderers\Html;

use InvalidArgumentException;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer;
use phpDocumentor\Guides\RestructuredText\Nodes\CollectionNode;
use phpDocumentor\Guides\RestructuredText\Nodes\ContainerNode;

/** @implements NodeRenderer<CollectionNode> */
final class CollectionNodeRenderer implements NodeRenderer
{
    private Renderer $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof CollectionNode;
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        if ($node instanceof CollectionNode === false) {
            throw new InvalidArgumentException('Node must be an instance of ' . CollectionNode::class);
        }

        return $this->renderer->render(
            'body/collection.html.twig',
            [
                'node' => $node->getValue(),
            ]
        );
    }
}
