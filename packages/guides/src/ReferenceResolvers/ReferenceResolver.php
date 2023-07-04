<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;

interface ReferenceResolver
{
    /** @return bool true if the reference is resolved */
    public function resolve(LinkInlineNode $node, RenderContext $renderContext): bool;

    public static function getPriority(): int;
}
