<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

/**
 * Rule to parse for named references
 *
 * Syntax examples:
 *
 *     `Sample reference`_
 *     `Another example <https://phpdoc.org>`_
 *
 * @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#hyperlink-references
 */
class NamedPhraseRule extends ReferenceRule
{
    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::BACKTICK;
    }

    public function apply(BlockContext $blockContext, InlineLexer $lexer): InlineNode|null
    {
        $text = '';
        $embeddedUrl = null;
        $initialPosition = $lexer->token?->position;
        $lexer->moveNext();
        while ($lexer->token !== null) {
            switch ($lexer->token->type) {
                case InlineLexer::BACKTICK:
                    $lexer->moveNext();
                    if ($lexer->token?->type !== InlineLexer::UNDERSCORE) {
                        $this->rollback($lexer, $initialPosition ?? 0);

                        return null;
                    }

                    $lexer->moveNext();
                    if ($text === '') {
                        $text = $embeddedUrl ?? '';
                    }

                    return $this->createReference($blockContext, $text, $embeddedUrl);

                case InlineLexer::EMBEDED_URL_START:
                    $embeddedUrl = $this->parseEmbeddedUrl($lexer);
                    if ($embeddedUrl === null) {
                        $text .= '<';
                    }

                    break;
                case InlineLexer::WHITESPACE:
                    $text .= ' ';

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
