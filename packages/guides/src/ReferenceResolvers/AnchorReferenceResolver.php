<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;

use function filter_var;

use const FILTER_VALIDATE_EMAIL;

/**
 * Resolves references with an anchor URL.
 *
 * A link is an anchor if it starts with a hashtag
 */
class AnchorReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = -100;

    public function resolve(LinkInlineNode $node, RenderContext $renderContext): bool
    {
        if (filter_var($node->getTargetReference(), FILTER_VALIDATE_EMAIL)) {
            $node->setUrl('mailto:' . $node->getTargetReference());

            return true;
        }

        return false;
    }

    public static function getPriority(): int
    {
        return self::PRIORITY;
    }
}
