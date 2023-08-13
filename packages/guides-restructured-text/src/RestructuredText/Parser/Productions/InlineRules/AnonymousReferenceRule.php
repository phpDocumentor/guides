<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
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
class AnonymousReferenceRule extends ReferenceRule
{
    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::ANONYMOUSE_REFERENCE;
    }

    public function apply(BlockContext $blockContext, InlineLexer $lexer): HyperLinkNode|null
    {
        $node = $this->createAnonymousReference(
            $blockContext,
            trim((string) $lexer->token?->value, '_'),
        );
        $lexer->moveNext();

        return $node;
    }

    private function createAnonymousReference(BlockContext $blockContext, string $link): HyperLinkNode
    {
        $blockContext->getDocumentParserContext()->getContext()->resetAnonymousStack();
        $node = $this->createReference($blockContext, $link, null, false);
        $blockContext->getDocumentParserContext()->getContext()->pushAnonymous($link);

        return $node;
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
