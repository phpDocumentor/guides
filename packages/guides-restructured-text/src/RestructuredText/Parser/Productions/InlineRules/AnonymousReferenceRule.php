<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\InlineToken\HyperLinkNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

use function trim;

/**
 * Rule to parse for simple anonymous references, such as `myref__`
 */
class AnonymousReferenceRule extends ReferenceRule
{
    public function applies(SpanLexer $lexer): bool
    {
        return $lexer->token?->type === SpanLexer::ANONYMOUSE_REFERENCE;
    }

    public function apply(ParserContext $parserContext, SpanLexer $lexer): HyperLinkNode|null
    {
        $node = $this->createAnonymousReference(
            $parserContext,
            trim((string) $lexer->token?->value, '_'),
        );
        $lexer->moveNext();

        return $node;
    }

    private function createAnonymousReference(ParserContext $parserContext, string $link): HyperLinkNode
    {
        $parserContext->resetAnonymousStack();
        $node = $this->createReference($parserContext, $link);
        $parserContext->pushAnonymous($link);

        return $node;
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
