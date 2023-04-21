<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\References\Resolver;

use phpDocumentor\Guides\Nodes\InlineToken\CrossReferenceNode;
use phpDocumentor\Guides\References\ResolvedReference;
use phpDocumentor\Guides\RenderContext;

interface Resolver
{
    public function supports(CrossReferenceNode $node, RenderContext $context): bool;

    public function resolve(CrossReferenceNode $node, RenderContext $context): ResolvedReference|null;
}
