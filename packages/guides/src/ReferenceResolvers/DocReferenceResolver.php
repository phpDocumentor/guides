<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;

class DocReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = 1000;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly DocumentNameResolverInterface $documentNameResolver,
    ) {
    }

    public function resolve(LinkInlineNode $node, RenderContext $renderContext): bool
    {
        if (!$node instanceof DocReferenceNode) {
            return false;
        }

        $document = $renderContext->getProjectNode()->findDocumentEntry(
            $this->documentNameResolver->canonicalUrl($renderContext->getDirName(), $node->getTargetReference()),
        );
        if ($document === null) {
            return false;
        }

        $node->setUrl($this->urlGenerator->generateCanonicalOutputUrl($renderContext, $document->getFile()));
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
