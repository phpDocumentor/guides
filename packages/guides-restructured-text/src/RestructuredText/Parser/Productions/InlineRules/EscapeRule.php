<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\NbspToken;
use phpDocumentor\Guides\Nodes\Inline\NewlineNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextToken;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

use function substr;

/**
 * Rule to escape characters with a backslash
 */
class EscapeRule extends ReferenceRule
{
    public function applies(SpanLexer $lexer): bool
    {
        return $lexer->token?->type === SpanLexer::ESCAPED_SIGN;
    }

    public function apply(ParserContext $parserContext, SpanLexer $lexer): NewlineNode|NbspToken|PlainTextToken
    {
        $char = $lexer->token?->value ?? '';
        $char = substr($char, 1, 1);
        $lexer->moveNext();

        if ($char === "\n") {
            return new NewlineNode();
        }

        if ($char === ' ') {
            return new NbspToken();
        }

        return new PlainTextToken($char);
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
