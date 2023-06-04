<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Compiler\NodeTransformers\NodeTransformerFactory;
use phpDocumentor\Guides\Compiler\ShadowTree\TreeNode;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;

final class DocumentNodeTraverser
{
    public function __construct(
        private readonly NodeTransformerFactory $nodeTransformerFactory,
        private readonly int $priority,
    ) {
    }

    public function traverse(DocumentNode $node, CompilerContext $compilerContext): Node|null
    {
        $traversedNode = $node;
        foreach ($this->nodeTransformerFactory->getTransformers() as $transformer) {
            if ($transformer->getPriority() !== $this->priority) {
                continue;
            }

            $traversedNode = $this->traverseForTransformer($transformer, $compilerContext->getShadowTree(), $compilerContext);
            if ($traversedNode === null) {
                return null;
            }
        }

        return $traversedNode;
    }

    /**
     * @param NodeTransformer<Node> $transformer
     * @param TNode $node
     * return TNode|null
     *
     * @template TNode as Node
     */
    private function traverseForTransformer(
        NodeTransformer $transformer,
        TreeNode $shadowNode,
        CompilerContext $compilerContext,
    ): Node|null {
        $node = $shadowNode->getNode();
        $supports = $transformer->supports($node);

        if ($supports) {
            $transformed = $transformer->enterNode($node, $compilerContext);
            $shadowNode->getParent()?->replaceChild($node, $transformed);
        }

        foreach ($shadowNode->getChildren() as $key => $shadowChild) {
            $childNode = $shadowChild->getNode();
            $transformed = $this->traverseForTransformer($transformer, $shadowChild, $compilerContext);
            if ($transformed === null) {
                $shadowNode->removeChild($childNode);
                continue;
            }

            if ($transformed === $childNode) {
                continue;
            }

            $shadowNode->replaceChild($childNode, $transformed);
        }

        if ($supports) {
            $transformed = $transformer->leaveNode($node, $compilerContext);
            if ($transformed === null) {
                $shadowNode->getParent()?->removeChild($node);
            }
        }

        return $shadowNode->getNode();
    }
}
