<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;

use function rtrim;

/**
 * Resolves named and anonymous references to source files
 *
 * `Link Text <../page.rst>`_ or [Link Text](path/to/another/page.md)
 */
class PageHyperlinkResolver implements ReferenceResolver
{
    // Named links and anchors take precedence
    public final const PRIORITY = -200;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly DocumentNameResolverInterface $documentNameResolver,
    ) {
    }

    public function resolve(LinkInlineNode $node, RenderContext $renderContext): bool
    {
        if (!$node instanceof HyperLinkNode) {
            return false;
        }

        $canonicalDocumentName = $this->documentNameResolver->canonicalUrl($renderContext->getDirName(), $node->getTargetReference());
        $canonicalDocumentName = rtrim($canonicalDocumentName, '.' . $renderContext->getOutputFormat());
        $canonicalDocumentName = rtrim($canonicalDocumentName, '.rst');
        $canonicalDocumentName = rtrim($canonicalDocumentName, '.md');
        $document = $renderContext->getProjectNode()->findDocumentEntry($canonicalDocumentName);
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
