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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

use function trim;

/**
 * Rule to parse for simple anonymous references
 *
 * Syntax example:
 *
 *     Example reference__
 *
 * @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#anonymous-hyperlinks
 */
final class AnonymousReferenceRule extends ReferenceRule implements CachableInlineRule
{
    public function getToken(): int
    {
        return InlineLexer::ANONYMOUSE_REFERENCE;
    }

    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::ANONYMOUSE_REFERENCE;
    }

    public function apply(BlockContext $blockContext, InlineLexer $lexer): AbstractLinkInlineNode|null
    {
        $node = $this->createAnonymousReference(
            $blockContext,
            trim((string) $lexer->token?->value, '_'),
        );
        $lexer->moveNext();

        return $node;
    }

    private function createAnonymousReference(BlockContext $blockContext, string $link): AbstractLinkInlineNode
    {
        $node = $this->createReference($blockContext, $link, null, false);
        $blockContext->getDocumentParserContext()->pushAnonymous($link);

        return $node;
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
