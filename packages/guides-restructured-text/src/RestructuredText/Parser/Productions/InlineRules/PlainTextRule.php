<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\InlineMarkupToken;
use phpDocumentor\Guides\Nodes\Inline\PlainTextToken;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

class PlainTextRule implements InlineRule
{
    public function applies(SpanLexer $lexer): bool
    {
        return true;
    }

    public function apply(ParserContext $parserContext, SpanLexer $lexer): InlineMarkupToken|null
    {
        $node = new PlainTextToken($lexer->token?->value ?? '');
        $lexer->moveNext();

        return $node;
    }

    public function getPriority(): int
    {
        // Must come last as it catches all
        return 0;
    }
}
