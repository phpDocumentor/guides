<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;

use function sprintf;

class DocReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = 1000;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly DocumentNameResolverInterface $documentNameResolver,
    ) {
    }

    public function resolve(LinkInlineNode $node, RenderContext $renderContext, Messages $messages): bool
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
            $messages->addWarning(new Message(sprintf(
                'Document with name "%s" not found, required in file "%s".',
                $canonicalDocumentName,
                $renderContext->getCurrentFileName(),
            )));

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
