<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\InlineMarkupToken;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

class InternalReferenceRule extends ReferenceRule
{
    public function applies(SpanLexer $lexer): bool
    {
        return $lexer->token?->type === SpanLexer::INTERNAL_REFERENCE_START;
    }

    public function apply(ParserContext $parserContext, SpanLexer $lexer): InlineMarkupToken|null
    {
        $text = '';
        $initialPosition = $lexer->token?->position;
        $lexer->moveNext();
        while ($lexer->token !== null) {
            switch ($lexer->token->type) {
                case SpanLexer::BACKTICK:
                    $lexer->moveNext();

                    return $this->createReference($parserContext, $text);

                default:
                    $text .= $lexer->token->value;
            }

            $lexer->moveNext();
        }

        $lexer->resetPosition($initialPosition ?? 0);
        $lexer->moveNext();
        $lexer->moveNext();

        return null;
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
