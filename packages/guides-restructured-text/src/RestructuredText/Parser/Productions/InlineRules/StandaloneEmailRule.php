<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

/**
 * Rule for standalone hyperlinks
 *
 * Syntax example:
 *
 *     phpdoc@example.org
 *
 * @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#standalone-hyperlinks
 */
class StandaloneEmailRule extends ReferenceRule implements MatchCachable
{
    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::EMAIL;
    }

    public function apply(BlockContext $blockContext, InlineLexer $lexer): AbstractLinkInlineNode|null
    {
        $value = $lexer->token?->value ?? '';
        $node = $this->createReference($blockContext, $value, $value, false);

        $lexer->moveNext();

        return $node;
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function isCacheable(): bool
    {
        return true;
    }
}
