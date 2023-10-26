<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;

/**
 * Resolves references with an anchor URL.
 *
 * A link is an anchor if it starts with a hashtag
 */
class AnchorReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = -100;

    public function __construct(
        private readonly AnchorReducer $anchorReducer,
    ) {
    }

    public function resolve(LinkInlineNode $node, RenderContext $renderContext): bool
    {
        $reducedAnchor = $this->anchorReducer->reduceAnchor($node->getTargetReference());
        $target = $renderContext->getProjectNode()->getInternalTarget($reducedAnchor);
        if ($target === null) {
            return false;
        }

        $node->setUrl($renderContext->generateCanonicalOutputUrl($target->getDocumentPath(), $target->getAnchor()));
        if ($node->getValue() === '') {
            $node->setValue($target->getTitle() ?? '');
        }

        return true;
    }

    public static function getPriority(): int
    {
        return self::PRIORITY;
    }
}
