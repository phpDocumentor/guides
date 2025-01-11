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

use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;

use function count;
use function str_ends_with;
use function strlen;
use function substr;

/**
 * Resolves named and anonymous references to source files
 *
 * `Link Text <../page.rst>`_ or [Link Text](path/to/another/page.md)
 */
final class PageHyperlinkResolver implements ReferenceResolver
{
    // Named links and anchors take precedence
    public final const PRIORITY = -200;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly DocumentNameResolverInterface $documentNameResolver,
    ) {
    }

    public function resolve(LinkInlineNode $node, RenderContext $renderContext, Messages $messages): bool
    {
        if (!$node instanceof HyperLinkNode) {
            return false;
        }

        $canonicalDocumentName = $this->documentNameResolver->canonicalUrl($renderContext->getDirName(), $node->getTargetReference());
        if (str_ends_with($canonicalDocumentName, '.' . $renderContext->getOutputFormat())) {
            $canonicalDocumentName = substr($canonicalDocumentName, 0, 0 - strlen('.' . $renderContext->getOutputFormat()));
        }

        $document = $renderContext->getProjectNode()->findDocumentEntry($canonicalDocumentName);
        if ($document === null) {
            return false;
        }

        $node->setUrl($this->urlGenerator->generateCanonicalOutputUrl($renderContext, $document->getFile()));
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
