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
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactoryAware;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RenderContext;

use function assert;
use function count;
use function implode;
use function is_a;
use function max;

/** @implements NodeRenderer<TableNode> */
final class TableNodeRenderer implements NodeRenderer, NodeRendererFactoryAware
{
    private NodeRendererFactory|null $nodeRendererFactory = null;

    public function setNodeRendererFactory(NodeRendererFactory $nodeRendererFactory): void
    {
        $this->nodeRendererFactory = $nodeRendererFactory;
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        if ($node instanceof TableNode === false) {
            throw new InvalidArgumentException('Invalid node presented');
        }

        $cols = 0;

        $rows = [];
        foreach ($node->getData() as $row) {
            $rowTex = '';
            $cols = max($cols, count($row->getColumns()));

            assert($this->nodeRendererFactory !== null);
            foreach ($row->getColumns() as $n => $col) {
                assert($col instanceof SpanNode);
                $rowTex .= $this->nodeRendererFactory->get($col)->render($col, $renderContext);

                if ((int) $n + 1 >= count($row->getColumns())) {
                    continue;
                }

                $rowTex .= ' & ';
            }

            $rowTex .= ' \\\\' . "\n";
            $rows[] = $rowTex;
        }

        $aligns = [];
        for ($i = 0; $i < $cols; $i++) {
            $aligns[] = 'l';
        }

        $aligns = '|' . implode('|', $aligns) . '|';
        $rows = "\\hline\n" . implode("\\hline\n", $rows) . "\\hline\n";

        return "\\begin{tabular}{" . $aligns . "}\n" . $rows . "\n\\end{tabular}\n";
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === TableNode::class || is_a($nodeFqcn, TableNode::class, true);
    }
}
