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
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;

use function count;
use function str_starts_with;

/**
 * Resolves references with an anchor URL.
 *
 * Looks up the anchor in the project's internal targets and produces a
 * canonical URL. For fragment-only references (starting with #) that don't
 * match any known target, falls back to a bare fragment URL.
 */
final class AnchorHyperlinkResolver implements ReferenceResolver
{
    public final const PRIORITY = -100;

    public function __construct(
        private readonly AnchorNormalizer $anchorReducer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function resolve(LinkInlineNode $node, RenderContext $renderContext, Messages $messages): bool
    {
        if (!$node instanceof HyperLinkNode) {
            return false;
        }

        $reducedAnchor = $this->anchorReducer->reduceAnchor($node->getTargetReference());
        $target = $renderContext->getProjectNode()->getInternalTarget($reducedAnchor);

        if ($target === null) {
            $target = $renderContext->getProjectNode()->getInternalTarget($reducedAnchor, SectionNode::STD_TITLE);
            if ($target === null) {
                if (str_starts_with($node->getTargetReference(), '#')) {
                    $node->setUrl($node->getTargetReference());

                    return true;
                }

                return false;
            }
        }

        $node->setUrl($this->urlGenerator->generateCanonicalOutputUrl($renderContext, $target->getDocumentPath(), $target->getAnchor()));
        if (count($node->getChildren()) === 0) {
            $node->addChildNode(new PlainTextInlineNode($target->getTitle() ?? ''));
        }

        return true;
    }

    public static function getPriority(): int
    {
        return self::PRIORITY;
    }
}
