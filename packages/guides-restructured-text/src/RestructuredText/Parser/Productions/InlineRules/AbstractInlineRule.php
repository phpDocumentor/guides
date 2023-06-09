<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

abstract class AbstractInlineRule implements InlineRule
{
    protected function rollback(InlineLexer $lexer, int $position): void
    {
        $lexer->resetPosition($position);
        $lexer->moveNext();
        $lexer->moveNext();
    }
}
