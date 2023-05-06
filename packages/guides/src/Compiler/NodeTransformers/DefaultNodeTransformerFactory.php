<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\Node;

class DefaultNodeTransformerFactory implements NodeTransformerFactory
{
    public function __construct(private readonly Metas $metas)
    {
    }

    /** @return iterable<NodeTransformer<Node>> */
    public function getTransformers(): iterable
    {
        /** @var iterable<NodeTransformer<Node>> $transformers */
        $transformers = [
            new MenuNodeTransformer($this->metas),
            new CollectLinkTargetsTransformer($this->metas),
            new ClassNodeTransformer(),
        ];

        return $transformers;
    }
}
