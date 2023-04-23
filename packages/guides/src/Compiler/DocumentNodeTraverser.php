<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Compiler\NodeTransformers\NodeTransformerFactory;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;

final class DocumentNodeTraverser
{
    public function __construct(private readonly NodeTransformerFactory $nodeTransformerFactory)
    {
    }

    public function traverse(DocumentNode $node): Node|null
    {
        foreach ($this->nodeTransformerFactory->getTransformers() as $transformer) {
            $node = $this->traverseForTransformer($transformer, $node);
            if ($node === null) {
                return null;
            }
        }

        return $node;
    }

    /**
     * @param NodeTransformer<Node> $transformer
     * @param TNode $node
     * return TNode|null
     *
     * @template TNode as Node
     */
    private function traverseForTransformer(NodeTransformer $transformer, Node $node): Node|null
    {
        $supports = $transformer->supports($node);

        if ($supports) {
            $node = $transformer->enterNode($node);
        }

        if ($node instanceof CompoundNode) {
            foreach ($node->getChildren() as $key => $childNode) {
                $transformed = $this->traverseForTransformer($transformer, $childNode);
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
            $node = $transformer->leaveNode($node);
        }

        return $node;
    }
}
