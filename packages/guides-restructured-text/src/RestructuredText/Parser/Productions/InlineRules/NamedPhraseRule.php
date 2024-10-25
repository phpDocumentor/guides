<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\InlineNodeInterface;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;
use phpDocumentor\Guides\RestructuredText\Parser\References\EmbeddedReferenceParser;

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
final class NamedPhraseRule extends ReferenceRule
{
    use EmbeddedReferenceParser;

    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::BACKTICK;
    }

    public function apply(BlockContext $blockContext, InlineLexer $lexer): InlineNodeInterface|null
    {
        $value = '';
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

                    $referenceData = $this->extractEmbeddedReference($value);

                    return $this->createReference($blockContext, $referenceData->reference, $referenceData->text);

                case InlineLexer::WHITESPACE:
                    $value .= ' ';

                    break;
                default:
                    $value .= $lexer->token->value;
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
