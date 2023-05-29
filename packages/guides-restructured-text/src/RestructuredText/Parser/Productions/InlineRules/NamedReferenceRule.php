<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\InlineToken\HyperLinkNode;
use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

use function preg_replace;
use function str_replace;
use function trim;

class NamedReferenceRule implements InlineRule
{
    public function applies(SpanLexer $lexer): bool
    {
        return $lexer->token?->type === SpanLexer::NAMED_REFERENCE;
    }

    public function apply(ParserContext $parserContext, SpanLexer $lexer): InlineMarkupToken|null
    {
        if ($lexer->token?->type === SpanLexer::NAMED_REFERENCE) {
            $node = $this->createNamedReference($parserContext, trim($lexer->token->value, '_'));
            $lexer->moveNext();

            return $node;
        }

        return null;
    }

    private function createNamedReference(ParserContext $parserContext, string $link, string|null $url = null): HyperLinkNode
    {
        // the link may have a new line in it, so we need to strip it
        // before setting the link and adding a token to be replaced
        $link = str_replace("\n", ' ', $link);
        $link = trim(preg_replace('/\s+/', ' ', $link) ?? '');

        if ($url !== null) {
            $parserContext->setLink($link, $url);
        }

        return new HyperLinkNode('', $link, $url ?? '');
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
