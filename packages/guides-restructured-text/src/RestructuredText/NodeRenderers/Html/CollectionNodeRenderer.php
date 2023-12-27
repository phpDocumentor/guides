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
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

use function is_a;

/** @implements NodeRenderer<CollectionNode> */
final class CollectionNodeRenderer implements NodeRenderer
{
    public function __construct(private readonly TemplateRenderer $renderer)
    {
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === CollectionNode::class || is_a($nodeFqcn, CollectionNode::class, true);
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        if ($node instanceof CollectionNode === false) {
            throw new InvalidArgumentException('Node must be an instance of ' . CollectionNode::class);
        }

        return $this->renderer->renderTemplate(
            $renderContext,
            'body/collection.html.twig',
            [
                'node' => $node->getValue(),
            ],
        );
    }
}
