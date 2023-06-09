<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\InlineToken\CitationInlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\FootnoteInlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\AnnotationUtility;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

/**
 * Rule to parse for text roles such as ``:ref:`something` `
 */
class AnnotationRoleRule extends AbstractInlineRule
{
    public function applies(SpanLexer $lexer): bool
    {
        return $lexer->token?->type === SpanLexer::ANNOTATION_START;
    }

    public function apply(ParserContext $parserContext, SpanLexer $lexer): InlineMarkupToken|null
    {
        $startPosition = $lexer->token?->position;
        $annotationName = '';

        $lexer->moveNext();

        while ($lexer->token !== null) {
            $token = $lexer->token;
            switch ($token->type) {
                case SpanLexer::ANNOTATION_END:
                    // `]`  found, look for `_`
                    if (!$lexer->moveNext() && $lexer->token === null) {
                        break 2;
                    }

                    $token = $lexer->token;
                    if ($token->type === SpanLexer::UNDERSCORE) {
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
                case SpanLexer::WHITESPACE:
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
