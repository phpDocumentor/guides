<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;

class DocReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = 1000;

    public function resolve(LinkInlineNode $node, RenderContext $renderContext): bool
    {
        if (!$node instanceof DocReferenceNode) {
            return false;
        }

        $document = $renderContext->getProjectNode()->findDocumentEntry(
            $renderContext->canonicalUrl($node->getTargetReference()),
        );
        if ($document === null) {
            return false;
        }

        $node->setUrl($renderContext->generateCanonicalOutputUrl($document->getFile()));
        if ($node->getValue() === '') {
            $node->setValue($document->getTitle()->toString());
        }

        return true;
    }

    public static function getPriority(): int
    {
        return self::PRIORITY;
    }
}
