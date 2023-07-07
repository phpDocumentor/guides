<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

/**
 * Rule to parse for anonymous references
 *
 * Syntax example:
 *
 *     `Example anonymous reference`__
 *     `Example reference <http://phpdoc.org>`__
 *
 * @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#anonymous-hyperlinks
 */
class AnonymousPhraseRule extends ReferenceRule
{
    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::BACKTICK;
    }

    public function apply(ParserContext $parserContext, InlineLexer $lexer): HyperLinkNode|null
    {
        $text = '';
        $embeddedUrl = null;
        $initialPosition = $lexer->token?->position;
        $lexer->moveNext();
        while ($lexer->token !== null) {
            switch ($lexer->token->type) {
                case InlineLexer::PHRASE_ANONYMOUS_END:
                    $lexer->moveNext();

                    return $this->createAnonymousReference($parserContext, $text, $embeddedUrl);

                case InlineLexer::EMBEDED_URL_START:
                    $embeddedUrl = $this->parseEmbeddedUrl($lexer);
                    if ($embeddedUrl === null) {
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

    private function createAnonymousReference(ParserContext $parserContext, string $link, string|null $embeddedUrl): HyperLinkNode
    {
        $parserContext->resetAnonymousStack();
        $node = $this->createReference($parserContext, $link, $embeddedUrl, false);
        $parserContext->pushAnonymous($link);

        return $node;
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
