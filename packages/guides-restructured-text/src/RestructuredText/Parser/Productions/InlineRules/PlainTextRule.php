<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

class PlainTextRule implements InlineRule
{
    public function applies(SpanLexer $lexer): bool
    {
        return true;
    }

    public function apply(ParserContext $parserContext, SpanLexer $lexer): InlineNode|null
    {
        $node = new PlainTextInlineNode($lexer->token?->value ?? '');
        $lexer->moveNext();

        return $node;
    }

    public function getPriority(): int
    {
        // Must come last as it catches all
        return 0;
    }
}
