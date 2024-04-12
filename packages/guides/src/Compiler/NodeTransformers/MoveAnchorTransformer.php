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

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use ArrayIterator;
use LogicException;
use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Compiler\ShadowTree\TreeNode;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;
use WeakMap;

/** @implements NodeTransformer<AnchorNode> */
final class MoveAnchorTransformer implements NodeTransformer
{
    /** @var WeakMap<AnchorNode, true> */
    private WeakMap $seen;

    public function __construct()
    {
        $this->seen = new WeakMap();
    }

    public function enterNode(Node $node, CompilerContextInterface $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, CompilerContextInterface $compilerContext): Node|null
    {
        //When exists in seen, it means that the node has already been processed. Ignore it.
        if (isset($this->seen[$node])) {
            return $node;
        }

        $this->seen[$node] = true;
        $parent = $compilerContext->getShadowTree()->getParent();
        if ($parent === null) {
            throw new LogicException('Node not found in shadow tree');
        }

        $position = $parent->findPosition($node);
        if ($position === null) {
            throw new LogicException('Node not found in shadow tree');
        }

        return $this->attemptMoveToNeighbour($parent, $position, $node);
    }

    public function supports(Node $node): bool
    {
        return $node instanceof AnchorNode;
    }

    public function getPriority(): int
    {
        return 30_000;
    }

    /** @param TreeNode<Node> $parent */
    private function attemptMoveToNeighbour(TreeNode $parent, int $position, AnchorNode $node): AnchorNode|null
    {
        $current = $this->findNextSection($parent, $position);
        if ($current === null) {
            if ($parent->getParent() === null) {
                return $node;
            }

            $position = $parent->getParent()->findPosition($parent->getNode());
            if ($position === null) {
                throw new LogicException('Node not found in shadow tree');
            }

            return $this->attemptMoveToNeighbour($parent->getParent(), $position, $node);
        }

        if ($current->getNode() instanceof SectionNode) {
            $current->pushChild($node);

            return null;
        }

        return $node;
    }

    /**
     * @param TreeNode<Node> $parent
     *
     * @return TreeNode<Node>|null
     */
    private function findNextSection(TreeNode $parent, int $position): TreeNode|null
    {
        $children = new ArrayIterator($parent->getChildren());
        if ($children->count() <= $position + 1) {
            return null;
        }

        $children->seek($position + 1);
        while ($children->valid() && $children->current()->getNode() instanceof AnchorNode) {
            $children->next();
        }

        return $children->current();
    }
}
