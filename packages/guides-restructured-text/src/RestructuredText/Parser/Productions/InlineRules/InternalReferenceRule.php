<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

class InternalReferenceRule extends ReferenceRule
{
    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::INTERNAL_REFERENCE_START;
    }

    public function apply(ParserContext $parserContext, InlineLexer $lexer): InlineNode|null
    {
        $text = '';
        $initialPosition = $lexer->token?->position;
        $lexer->moveNext();
        while ($lexer->token !== null) {
            switch ($lexer->token->type) {
                case InlineLexer::BACKTICK:
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
