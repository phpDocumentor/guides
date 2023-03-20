<?php

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\Node;

final class CustomNodeTransformerFactory implements NodeTransformerFactory
{

    /** @var iterable<NodeTransformer<Node>> */
    private iterable $transformers;

    /**
     * @param iterable<NodeTransformer<Node>> $transformers
     */
    public function __construct(iterable $transformers)
    {
        $this->transformers = $transformers;
    }

    /** @return iterable<NodeTransformer<Node>> */
    public function getTransformers(): iterable
    {
        return $this->transformers;
    }
}
