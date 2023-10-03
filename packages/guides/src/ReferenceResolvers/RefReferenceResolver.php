<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\RenderContext;

class RefReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = 1000;

    public function resolve(LinkInlineNode $node, RenderContext $renderContext): bool
    {
        if (!$node instanceof ReferenceNode) {
            return false;
        }

        $target = $renderContext->getProjectNode()->getInternalTarget($node->getTargetReference());
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
