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

use LogicException;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer;
use Webmozart\Assert\Assert;

use function sprintf;

/** @implements NodeRenderer<TableNode> */
class TableNodeRenderer implements NodeRenderer
{
    private Renderer $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(Node $node, RenderContext $environment): string
    {
        $headers = $node->getHeaders();
        $rows = $node->getData();

        return $this->renderer->render(
            'table.html.twig',
            [
                'tableNode' => $node,
                'tableHeaderRows' => $headers,
                'tableRows' => $rows,
            ]
        );
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TableNode;
    }
}
