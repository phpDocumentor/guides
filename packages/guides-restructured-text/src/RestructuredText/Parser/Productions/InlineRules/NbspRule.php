<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\WhitespaceInlineNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

/**
 * Rule to parse for non-breaking spaces: a~b
 */
class NbspRule extends ReferenceRule
{
    public function applies(SpanLexer $lexer): bool
    {
        return $lexer->token?->type === SpanLexer::NBSP;
    }

    public function apply(ParserContext $parserContext, SpanLexer $lexer): WhitespaceInlineNode
    {
        $lexer->moveNext();

        return new WhitespaceInlineNode();
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
