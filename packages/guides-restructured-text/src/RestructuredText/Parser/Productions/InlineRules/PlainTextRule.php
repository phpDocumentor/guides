<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

class PlainTextRule implements InlineRule
{
    public function applies(InlineLexer $lexer): bool
    {
        return true;
    }

    public function apply(BlockContext $blockContext, InlineLexer $lexer): InlineNode|null
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
