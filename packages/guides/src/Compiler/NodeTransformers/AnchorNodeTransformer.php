<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitledNode;

/**
 * @implements NodeTransformer<Node>
 *
 * A custom anchor, also called label, can be applied to sections if directly in front of a headline. It can be applied
 * directly to a figure as well. In all other cases it is a standalone target rendered separately, which does not have
 * a title.
 *
 * https://www.sphinx-doc.org/en/master/usage/restructuredtext/roles.html#role-ref
 */
class AnchorNodeTransformer implements NodeTransformer
{
    /** @var AnchorNode[] */
    private array $anchorNodes = [];

    public function enterNode(Node $node): Node
    {
        if ($node instanceof DocumentNode) {
            // unset classes when entering the next document
            $this->anchorNodes = [];
        }

        if ($node instanceof AnchorNode) {
            // collect all anchors, multiple anchors can be applied to one section
            $this->anchorNodes[] = $node;
        } elseif ($node instanceof TitledNode) {
            // Titled Nodes handle anchors themselves and provide a title to links
            foreach ($this->anchorNodes as $anchorNode) {
                $node->addAnchor((string) $anchorNode->getValue());
                $anchorNode->setParentNode($node);
                // reset the anchor nodes as they have already been added to a titled node
                $this->anchorNodes = [];
            }
        }

        return $node;
    }

    public function leaveNode(Node $node): Node|null
    {
        return $node;
    }

    public function supports(Node $node): bool
    {
        return true;
    }
}
