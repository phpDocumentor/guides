<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\References\Resolver;

use phpDocumentor\Guides\Nodes\InlineToken\DocReferenceNode;
use phpDocumentor\Guides\References\ResolvedReference;
use phpDocumentor\Guides\RenderContext;

final class RefResolver implements Resolver
{
    public function supports(DocReferenceNode $node, RenderContext $context): bool
    {
        return $node->getRole() === 'ref';
    }

    public function resolve(DocReferenceNode $node, RenderContext $context): ResolvedReference|null
    {
        $url = $node->getDocumentLink();

        $target = $context->getMetas()->getInternalTarget($url);
        if ($target === null) {
            return null;
        }

        $filePath = $context->canonicalUrl($target->getDocumentPath());
        if ($filePath === null) {
            return null;
        }

        return new ResolvedReference($url, $node->getText(), $context->relativeDocUrl($filePath, $target->getAnchor()));
    }
}
