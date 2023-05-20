<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\References\Resolver;

use phpDocumentor\Guides\Nodes\InlineToken\DocReferenceNode;
use phpDocumentor\Guides\References\ResolvedReference;
use phpDocumentor\Guides\RenderContext;

final class DocResolver implements Resolver
{
    public function supports(DocReferenceNode $node, RenderContext $context): bool
    {
        return true;
    }

    public function resolve(DocReferenceNode $node, RenderContext $context): ResolvedReference|null
    {
        $filePath = $context->canonicalUrl($node->getDocumentLink());

        if ($filePath === null) {
            return null;
        }

        $entry = $context->getMetas()->findDocument($filePath);
        if ($entry === null) {
            return null;
        }

        return $this->createResolvedReference(
            $node->getDocumentLink(),
            $context,
            $node->getText($entry->getTitle()->toString()),
            [],
            $node->getAnchor(),
        );
    }

    /**
     * @param string[] $attributes
     *
     * TODO refactor this... I see too many arguments, Why would you use the titles?
     */
    private function createResolvedReference(
        string $file,
        RenderContext $renderContext,
        string $text,
        array $attributes = [],
        string|null $anchor = null,
    ): ResolvedReference {
        return new ResolvedReference(
            $file,
            $text,
            $renderContext->relativeDocUrl($file, $anchor),
            $attributes,
        );
    }
}
