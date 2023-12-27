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
use phpDocumentor\Guides\RestructuredText\Nodes\SidebarNode;
use phpDocumentor\Guides\TemplateRenderer;

use function is_a;

/** @implements NodeRenderer<SidebarNode> */
final class SidebarNodeRenderer implements NodeRenderer
{
    public function __construct(private readonly TemplateRenderer $renderer)
    {
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === SidebarNode::class || is_a($nodeFqcn, SidebarNode::class, true);
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        if ($node instanceof SidebarNode === false) {
            throw new InvalidArgumentException('Node must be an instance of ' . SidebarNode::class);
        }

        return $this->renderer->renderTemplate(
            $renderContext,
            'structure/sidebar.html.twig',
            [
                'title' => $node->getTitle(),
                'node' => $node->getValue(),
            ],
        );
    }
}
