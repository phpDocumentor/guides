<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

use function array_merge;
use function sprintf;

class DocReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = 1000;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly DocumentNameResolverInterface $documentNameResolver,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function resolve(LinkInlineNode $node, RenderContext $renderContext): bool
    {
        if (!$node instanceof DocReferenceNode) {
            return false;
        }

        if ($node->getInterlinkDomain() !== '') {
            return false;
        }

        $canonicalDocumentName = $this->documentNameResolver->canonicalUrl($renderContext->getDirName(), $node->getTargetReference());

        $document = $renderContext->getProjectNode()->findDocumentEntry($canonicalDocumentName);
        if ($document === null) {
            $didYouMean = '';
            $document = $renderContext->getProjectNode()->findProposedDocumentEntry($canonicalDocumentName);
            if ($document !== null) {
                $didYouMean = sprintf('Did you mean "%s"?', $document->getFile());
            }

            $this->logger->warning(
                sprintf(
                    'Document with name "%s" not found, required in file "%s". %s',
                    $canonicalDocumentName,
                    $renderContext->getCurrentFileName(),
                    $didYouMean,
                ),
                array_merge($renderContext->getLoggerInformation(), $node->getDebugInformation()),
            );

            return true;
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
