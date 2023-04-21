<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\References\Resolver;

use phpDocumentor\Guides\Nodes\InlineToken\CrossReferenceNode;
use phpDocumentor\Guides\References\ResolvedReference;
use phpDocumentor\Guides\RenderContext;

final class RefResolver implements Resolver
{
    public function supports(CrossReferenceNode $node, RenderContext $context): bool
    {
        return $node->getRole() === 'ref';
    }

    public function resolve(CrossReferenceNode $node, RenderContext $context): ResolvedReference|null
    {
        $url = $node->getUrl();

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
