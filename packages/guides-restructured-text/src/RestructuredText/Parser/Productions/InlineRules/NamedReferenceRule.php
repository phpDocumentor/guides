<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

use function trim;

/**
 * Rule to parse for simple named references, such as `myref_`
 */
class NamedReferenceRule extends ReferenceRule
{
    public function applies(SpanLexer $lexer): bool
    {
        return $lexer->token?->type === SpanLexer::NAMED_REFERENCE;
    }

    public function apply(ParserContext $parserContext, SpanLexer $lexer): InlineMarkupToken|null
    {
        $node = $this->createReference($parserContext, trim($lexer->token?->value ?? '', '_'));
        $lexer->moveNext();

        return $node;
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
