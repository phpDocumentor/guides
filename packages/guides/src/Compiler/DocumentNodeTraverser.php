<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Compiler\NodeTransformers\NodeTransformerFactory;
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

            $traversedNode = $this->traverseForTransformer($transformer, $node, $compilerContext);
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
        Node $node,
        CompilerContext $compilerContext,
    ): Node|null {
        $supports = $transformer->supports($node);

        if ($supports) {
            $node = $transformer->enterNode($node, $compilerContext);
        }

        if ($node instanceof CompoundNode) {
            foreach ($node->getChildren() as $key => $childNode) {
                $transformed = $this->traverseForTransformer($transformer, $childNode, $compilerContext);
                if ($transformed === null) {
                    $node = $node->removeNode($key);
                    continue;
                }

                if ($transformed === $childNode) {
                    continue;
                }

                $node = $node->replaceNode($key, $transformed);
            }
        }

        if ($supports) {
            $node = $transformer->leaveNode($node, $compilerContext);
        }

        return $node;
    }
}
