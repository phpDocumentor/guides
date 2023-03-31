<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\References\Resolver;

use phpDocumentor\Guides\Nodes\InlineToken\CrossReferenceNode;
use phpDocumentor\Guides\References\ResolvedReference;
use phpDocumentor\Guides\RenderContext;

final class DocResolver implements Resolver
{
    public function supports(CrossReferenceNode $node, RenderContext $context): bool
    {
        return $node->getRole() === 'doc';
    }

    public function resolve(CrossReferenceNode $node, RenderContext $context): ?ResolvedReference
    {
        $filePath = $context->canonicalUrl($node->getUrl());

        if ($filePath === null) {
            return null;
        }

        $entry = $context->getMetas()->findDocument($filePath);
        if ($entry === null) {
            return null;
        }

        return $this->createResolvedReference(
            $node->getUrl(),
            $context,
            $node->getText($entry->getTitle()->toString()),
            [],
            $node->getAnchor()
        );
    }

    /**
     * @param string[] $attributes
     *
     * TODO refactor this... I see too many arguments, Why would you use the titles?
     */
    private function createResolvedReference(
        string        $file,
        RenderContext $renderContext,
        string $text,
        array         $attributes = [],
        ?string       $anchor = null
    ): ResolvedReference {
        return new ResolvedReference(
            $file,
            $text,
            $renderContext->relativeDocUrl($file, $anchor),
            $attributes
        );
    }
}
