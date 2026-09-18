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

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;

use function count;

/**
 * Resolves references with an anchor URL.
 *
 * A link is an anchor if it starts with a hashtag
 */
final class AnchorReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = -100;

    public function __construct(
        private readonly AnchorNormalizer $anchorReducer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function resolve(LinkInlineNode $node, RenderContext $renderContext, Messages $messages): bool
    {
        if (!$node instanceof ReferenceNode || $node->getInterlinkDomain() !== '') {
            return false;
        }

        $reducedAnchor = $this->anchorReducer->reduceAnchor($node->getTargetReference());
        $target = $renderContext->getProjectNode()->getInternalTarget($reducedAnchor, $node->getLinkType());

        if ($target === null) {
            return false;
        }

        $node->setUrl($this->urlGenerator->generateCanonicalOutputUrl($renderContext, $target->getDocumentPath(), $target->getPrefix() . $target->getAnchor()));
        if (count($node->getChildren()) === 0) {
            // A target only carries a title when it is a section, or an anchor whose parent
            // in the shadow tree is one. A label declared anywhere else - inside a directive
            // body, for instance - has none, and an empty string leaves the link without an
            // accessible name. Fall back to what the author wrote, as the unresolved-reference
            // marker does, so the link always says where it goes.
            $node->addChildNode(new PlainTextInlineNode($target->getTitle() ?? $node->getTargetReference()));
        }

        return true;
    }

    public static function getPriority(): int
    {
        return self::PRIORITY;
    }
}
