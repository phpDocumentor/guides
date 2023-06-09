<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\LiteralToken;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

use function strlen;
use function substr;

/**
 * Rule for literals such as ``something``
 */
class LiteralRule extends AbstractInlineRule
{
    public function applies(SpanLexer $lexer): bool
    {
        return $lexer->token?->type === SpanLexer::LITERAL;
    }

    public function apply(ParserContext $parserContext, SpanLexer $lexer): LiteralToken
    {
        $literal = $lexer->token?->value ?? '';
        if (strlen($literal) > 4) {
            $literal = substr($literal, 2, strlen($literal) - 4);
        }

        $lexer->moveNext();

        return new LiteralToken($literal);
    }

    public function getPriority(): int
    {
        // Should be executed first as any other rules within may not be interpreted
        return 10000;
    }
}
