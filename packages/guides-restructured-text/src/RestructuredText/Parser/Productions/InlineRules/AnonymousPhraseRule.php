<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\EmbeddedUriParser;
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
    use EmbeddedUriParser;

    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::BACKTICK;
    }

    public function apply(BlockContext $blockContext, InlineLexer $lexer): AbstractLinkInlineNode|null
    {
        $value = '';
        $initialPosition = $lexer->token?->position;
        $lexer->moveNext();
        while ($lexer->token !== null) {
            switch ($lexer->token->type) {
                case InlineLexer::BACKTICK:
                    $lexer->moveNext();
                    if ($lexer->token?->type !== InlineLexer::ANONYMOUS_END) {
                        $this->rollback($lexer, $initialPosition ?? 0);

                        return null;
                    }

                    $lexer->moveNext();

                    return $this->createAnonymousReference($blockContext, $value);

                default:
                    $value .= $lexer->token->value;
            }

            $lexer->moveNext();
        }

        $this->rollback($lexer, $initialPosition ?? 0);

        return null;
    }

    private function createAnonymousReference(BlockContext $blockContext, string $value): AbstractLinkInlineNode
    {
        $parsed = $this->extractEmbeddedUri($value);
        $link = $parsed['text'];
        $uri = $parsed['uri'];
        if ($link === null) {
            $link = $uri;
            $uri = null;
        }

        $node = $this->createReference($blockContext, $link, $uri, false);
        $blockContext->getDocumentParserContext()->pushAnonymous($link);

        return $node;
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
