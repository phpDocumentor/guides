<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;

class InternalReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = 100;

    public function resolve(LinkInlineNode $node, RenderContext $renderContext): bool
    {
        $link = $renderContext->getLink($node->getTargetReference());
        if ($link) {
            $node->setUrl($link);

            return true;
        }

        return false;
    }

    public static function getPriority(): int
    {
        return self::PRIORITY;
    }
}
