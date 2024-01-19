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

use phpDocumentor\Guides\Nodes\Inline\CitationInlineNode;
use phpDocumentor\Guides\Nodes\Inline\FootnoteInlineNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\AnnotationUtility;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

/**
 * Rule to parse for text roles such as ``:ref:`something` `
 */
final class AnnotationRoleRule extends AbstractInlineRule
{
    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::ANNOTATION_START;
    }

    public function apply(BlockContext $blockContext, InlineLexer $lexer): InlineNode|null
    {
        $startPosition = $lexer->token?->position;
        $annotationName = '';

        $lexer->moveNext();

        while ($lexer->token !== null) {
            $token = $lexer->token;
            switch ($token->type) {
                case InlineLexer::ANNOTATION_END:
                    // `]`  found, look for `_`
                    if (!$lexer->moveNext() && $lexer->token === null) {
                        break 2;
                    }

                    $token = $lexer->token;
                    if ($token->type === InlineLexer::UNDERSCORE) {
                        if (AnnotationUtility::isFootnoteKey($annotationName)) {
                            $number = AnnotationUtility::getFootnoteNumber($annotationName);
                            $name = AnnotationUtility::getFootnoteName($annotationName);
                            $node = new FootnoteInlineNode(
                                $annotationName,
                                $name ?? '',
                                $number ?? 0,
                            );
                        } else {
                            $node = new CitationInlineNode(
                                $annotationName,
                                $annotationName,
                            );
                        }

                        $lexer->moveNext();

                        return $node;
                    }

                    break 2;
                case InlineLexer::WHITESPACE:
                    // Annotation keys may not contain whitespace
                    break 2;
                default:
                    $annotationName .= $token->value;
            }

            if ($lexer->moveNext() === false && $lexer->token === null) {
                break;
            }
        }

        $this->rollback($lexer, $startPosition ?? 0);

        return null;
    }

    public function getPriority(): int
    {
        return 500;
    }
}
