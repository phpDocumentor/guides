<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\Node;

interface NodeTransformerFactory
{
    /** @return iterable<NodeTransformer<Node>> */
    public function getTransformers(): iterable;

    /** @return int[] */
    public function getPriorities(): array;
}
