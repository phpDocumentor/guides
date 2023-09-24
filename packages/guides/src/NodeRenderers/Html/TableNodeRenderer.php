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

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

/** @implements NodeRenderer<TableNode> */
class TableNodeRenderer implements NodeRenderer
{
    public function __construct(private readonly TemplateRenderer $renderer)
    {
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        $headers = $node->getHeaders();
        $rows = $node->getData();

        return $this->renderer->renderTemplate(
            $renderContext,
            'body/table.%s.twig',
            [
                'tableNode' => $node,
                'tableHeaderRows' => $headers,
                'tableRows' => $rows,
            ],
        );
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TableNode;
    }
}
