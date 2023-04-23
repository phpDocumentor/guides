<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\Node;

final class CustomNodeTransformerFactory implements NodeTransformerFactory
{
    /** @param iterable<NodeTransformer<Node>> $transformers */
    public function __construct(private readonly iterable $transformers)
    {
    }

    /** @return iterable<NodeTransformer<Node>> */
    public function getTransformers(): iterable
    {
        return $this->transformers;
    }
}
