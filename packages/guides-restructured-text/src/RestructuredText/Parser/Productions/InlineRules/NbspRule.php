<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\WhitespaceInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

/**
 * Rule to parse for non-breaking spaces: a~b
 */
final class NbspRule extends AbstractInlineRule implements MatchCachable
{
    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::NBSP;
    }

    public function apply(BlockContext $blockContext, InlineLexer $lexer): WhitespaceInlineNode
    {
        $lexer->moveNext();

        return new WhitespaceInlineNode();
    }

    public function getPriority(): int
    {
        return 1000;
    }

    public function isCacheable(): bool
    {
        return true;
    }
}
