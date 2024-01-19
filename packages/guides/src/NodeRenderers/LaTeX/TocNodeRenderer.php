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

namespace phpDocumentor\Guides\NodeRenderers\LaTeX;

use InvalidArgumentException;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

use function is_a;

/** @implements  NodeRenderer<TocNode> */
final class TocNodeRenderer implements NodeRenderer
{
    public function __construct(private readonly TemplateRenderer $renderer)
    {
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        if ($node instanceof TocNode === false) {
            throw new InvalidArgumentException('Invalid node presented');
        }

        return $this->renderer->renderTemplate(
            $renderContext,
            'toc.tex.twig',
            ['tocNode' => $node],
        );
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === TocNode::class || is_a($nodeFqcn, TocNode::class, true);
    }
}
