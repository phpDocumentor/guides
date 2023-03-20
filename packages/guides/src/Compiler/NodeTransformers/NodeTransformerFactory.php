<?php

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\Node;

interface NodeTransformerFactory
{
    /** @return iterable<NodeTransformer<Node>> */
    public function getTransformers(): iterable;
}
