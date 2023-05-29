<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\InlineToken\HyperLinkNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

/**
 * Rule to parse for simple anonymous references, such as `myref__`
 */
class AnonymousPhraseRule extends ReferenceRule
{
    public function applies(SpanLexer $lexer): bool
    {
        return $lexer->token?->type === SpanLexer::BACKTICK;
    }

    public function apply(ParserContext $parserContext, SpanLexer $lexer): HyperLinkNode|null
    {
        $text = '';
        $url = '';
        $initialPosition = $lexer->token?->position;
        $lexer->moveNext();
        while ($lexer->token !== null) {
            switch ($lexer->token->type) {
                case SpanLexer::PHRASE_ANONYMOUS_END:
                    $lexer->moveNext();

                    return $this->createReference($parserContext, $text, $url, false);

                case SpanLexer::EMBEDED_URL_START:
                    $url = $this->parseEmbeddedUrl($lexer);
                    if ($url === null) {
                        $text .= '<';
                    }

                    break;
                default:
                    $text .= $lexer->token->value;
            }

            $lexer->moveNext();
        }

        $this->rollback($lexer, $initialPosition ?? 0);

        return null;
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
