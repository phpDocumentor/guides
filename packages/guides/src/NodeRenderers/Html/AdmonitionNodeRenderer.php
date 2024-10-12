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

namespace phpDocumentor\Guides\NodeRenderers\Html;

use InvalidArgumentException;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\AdmonitionNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

use function implode;
use function is_a;

/** @implements NodeRenderer<AdmonitionNode> */
class AdmonitionNodeRenderer implements NodeRenderer
{
    public function __construct(private readonly TemplateRenderer $renderer)
    {
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === AdmonitionNode::class || is_a($nodeFqcn, AdmonitionNode::class, true);
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        if ($node instanceof AdmonitionNode === false) {
            throw new InvalidArgumentException('Node must be an instance of ' . AdmonitionNode::class);
        }

        $classes = $node->getClasses();

        return $this->renderer->renderTemplate(
            $renderContext,
            'body/admonition.html.twig',
            [
                'name' => $node->getName(),
                'text' => $node->getText(),
                'title' => $node->getTitle(),
                'isTitled' => $node->isTitled(),
                'class' => implode(' ', $classes),
                'node' => $node->getValue(),
            ],
        );
    }
}
