<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeTransformer;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;

class DocumentNodeTraverser
{
    /** @var iterable<NodeTransformer> */
    private $transformers;

    /**
     * @param iterable<NodeTransformer> $transformers
     */
    public function __construct(iterable $transformers)
    {
        $this->transformers = $transformers;
    }

    public function traverse(DocumentNode $node): Node
    {
        foreach ($this->transformers as $transformer) {
            $node = $this->traverseForTransformer($transformer, $node);
        }

        return $node;
    }

    private function traverseForTransformer(NodeTransformer $transformer, Node $node): Node
    {
        if ($supports = $transformer->supports($node)) {
            $node = $transformer->enterNode($node);
        }

        foreach ($node->getChildren() as $childNode) {
            $node = $this->traverseForTransformer($transformer, $childNode);
        }

        if ($supports) {
            $node = $transformer->leaveNode($node);
        }

        return $node;
    }
}
