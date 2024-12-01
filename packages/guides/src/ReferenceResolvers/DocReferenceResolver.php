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
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;

use function count;
use function explode;
use function sprintf;
use function str_contains;

final class DocReferenceResolver implements ReferenceResolver
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

        $targetReference = $node->getTargetReference();
        $anchor = '';
        if (str_contains($targetReference, '#')) {
            $exploded = explode('#', $targetReference, 2);
            $targetReference = $exploded[0];
            $anchor = '#' . $exploded[1];
        }

        $canonicalDocumentName = $this->documentNameResolver->canonicalUrl($renderContext->getDirName(), $targetReference);

        $document = $renderContext->getProjectNode()->findDocumentEntry($canonicalDocumentName);
        if ($document === null) {
            $messages->addWarning(new Message(sprintf(
                'Document with name "%s" not found, required in file "%s".',
                $canonicalDocumentName,
                $renderContext->getCurrentFileName(),
            )));

            return false;
        }

        $node->setUrl($this->urlGenerator->generateCanonicalOutputUrl($renderContext, $document->getFile()) . $anchor);
        if (count($node->getChildren()) === 0) {
            $node->addChildNode(new PlainTextInlineNode($document->getTitle()->toString()));
        }

        return true;
    }

    public static function getPriority(): int
    {
        return self::PRIORITY;
    }
}
