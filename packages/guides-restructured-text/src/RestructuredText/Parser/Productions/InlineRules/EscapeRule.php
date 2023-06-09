<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\NewlineInlineNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\Inline\WhitespaceInlineNode;
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

    public function apply(ParserContext $parserContext, SpanLexer $lexer): NewlineInlineNode|WhitespaceInlineNode|PlainTextInlineNode
    {
        $char = $lexer->token?->value ?? '';
        $char = substr($char, 1, 1);
        $lexer->moveNext();

        if ($char === "\n") {
            return new NewlineInlineNode();
        }

        if ($char === ' ') {
            return new WhitespaceInlineNode();
        }

        return new PlainTextInlineNode($char);
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
