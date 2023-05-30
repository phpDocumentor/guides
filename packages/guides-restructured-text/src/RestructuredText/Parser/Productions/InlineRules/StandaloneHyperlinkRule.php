<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\InlineToken\HyperLinkNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

/**
 * Rule to parse for simple anonymous references, such as `myref__`
 */
class StandaloneHyperlinkRule extends ReferenceRule
{
    public function applies(SpanLexer $lexer): bool
    {
        return $lexer->token?->type === SpanLexer::HYPERLINK;
    }

    public function apply(ParserContext $parserContext, SpanLexer $lexer): HyperLinkNode|null
    {
        $node = $this->createReference(
            $parserContext,
            $lexer->token?->value ?? '',
            null,
            false,
        );
        $lexer->moveNext();

        return $node;
    }

    public function getPriority(): int
    {
        return 100;
    }
}
