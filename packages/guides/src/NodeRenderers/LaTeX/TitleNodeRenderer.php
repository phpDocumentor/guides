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
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

use function is_a;

/** @implements NodeRenderer<TitleNode> */
final class TitleNodeRenderer implements NodeRenderer
{
    public function __construct(private readonly TemplateRenderer $renderer)
    {
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        if ($node instanceof TitleNode === false) {
            throw new InvalidArgumentException('Invalid node presented');
        }

        $type = 'chapter';

        if ($node->getLevel() > 1) {
            $type = 'section';

            for ($i = 2; $i < $node->getLevel(); $i++) {
                $type = 'sub' . $type;
            }
        }

        return $this->renderer->renderTemplate(
            $renderContext,
            'page/header/title.tex.twig',
            [
                'type' => $type,
                'titleNode' => $node,
            ],
        );
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === TitleNode::class || is_a($nodeFqcn, TitleNode::class, true);
    }
}
