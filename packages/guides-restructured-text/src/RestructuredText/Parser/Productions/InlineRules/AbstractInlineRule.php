<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

abstract class AbstractInlineRule implements InlineRule
{
    protected function rollback(SpanLexer $lexer, int $position): void
    {
        $lexer->resetPosition($position);
        $lexer->moveNext();
        $lexer->moveNext();
    }
}
