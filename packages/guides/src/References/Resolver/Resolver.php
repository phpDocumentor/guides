<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\References\Resolver;

use phpDocumentor\Guides\Nodes\InlineToken\DocReferenceNode;
use phpDocumentor\Guides\References\ResolvedReference;
use phpDocumentor\Guides\RenderContext;

interface Resolver
{
    public function supports(DocReferenceNode $node, RenderContext $context): bool;

    public function resolve(DocReferenceNode $node, RenderContext $context): ResolvedReference|null;
}
