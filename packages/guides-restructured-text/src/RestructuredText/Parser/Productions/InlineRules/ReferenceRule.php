<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

use function preg_replace;
use function str_replace;
use function trim;

abstract class ReferenceRule extends AbstractInlineRule
{
    protected function createReference(ParserContext $parserContext, string $link, string|null $url = null, bool $registerLink = true): HyperLinkNode
    {
        // the link may have a new line in it, so we need to strip it
        // before setting the link and adding a token to be replaced
        $link = str_replace("\n", ' ', $link);
        $link = trim(preg_replace('/\s+/', ' ', $link) ?? '');

        if ($registerLink && $url !== null) {
            $parserContext->setLink($link, $url);
        }

        return new HyperLinkNode($link, $url);
    }

    protected function parseEmbeddedUrl(SpanLexer $lexer): string|null
    {
        if ($lexer->token === null) {
            return null;
        }

        $startPosition = $lexer->token->position;
        $text = '';

        while ($lexer->moveNext()) {
            $token = $lexer->token;
            switch ($token->type) {
                case SpanLexer::NAMED_REFERENCE_END:
                    //We did not find the expected SpanLexer::EMBEDED_URL_END
                    $this->rollback($lexer, $startPosition);

                    return null;

                case SpanLexer::EMBEDED_URL_END:
                    return $text;

                default:
                    $text .= $token->value;
            }
        }

        $this->rollback($lexer, $startPosition);

        return null;
    }
}
