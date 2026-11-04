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

use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Compiler\ReverseNodeTransformer;
use phpDocumentor\Guides\Nodes\IncludedContentNode;
use phpDocumentor\Guides\Nodes\Node;

use const PHP_INT_MAX;

/** @implements ReverseNodeTransformer<IncludedContentNode> */
final class IncludeContentNodeTransformer implements ReverseNodeTransformer
{
    public function enterNode(Node $node, CompilerContextInterface $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, CompilerContextInterface $compilerContext): Node|null
    {
        $shadowNode = $compilerContext->getShadowTree();
        $parent = $shadowNode->getParent();
        $currentNode = $shadowNode->getNode();
        if ($parent !== null && $currentNode instanceof IncludedContentNode) {
            $parent->addChildrenBefore($currentNode, $currentNode->getChildren());
        }

        return null;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof IncludedContentNode;
    }

    public function getPriority(): int
    {
        // Include nodes must be expanded after section creation so nested included
        // sections are processed in the same context as their parent document.
        return PHP_INT_MAX - 2;
    }
}
