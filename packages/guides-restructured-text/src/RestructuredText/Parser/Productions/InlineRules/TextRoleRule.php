<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleFactory;

/**
 * Rule to parse for text roles such as ``:ref:`something` `
 */
class TextRoleRule extends AbstractInlineRule
{
    public function __construct(private readonly TextRoleFactory $textRoleFactory)
    {
    }

    public function applies(SpanLexer $lexer): bool
    {
        return $lexer->token?->type === SpanLexer::COLON;
    }

    public function apply(ParserContext $parserContext, SpanLexer $lexer): InlineNode|null
    {
        $domain = null;
        $role = null;
        $part = '';
        $inText = false;

        $initialPosition = $lexer->token?->position;
        $lexer->moveNext();

        while ($lexer->token !== null) {
            $token = $lexer->token;
            switch ($token->type) {
                case $token->type === SpanLexer::COLON && $inText === false:
                    if ($role !== null) {
                        $domain = $role;
                        $role = $part;
                        $part = '';
                        break;
                    }

                    $role = $part;
                    $part = '';
                    break;
                case SpanLexer::BACKTICK:
                    if ($role === null) {
                        break 2;
                    }

                    if ($inText) {
                        $textRole = $this->textRoleFactory->getTextRole($role, $domain);
                        $fullRole = ($domain ? $domain . ':' : '') . $role;
                        $lexer->moveNext();

                        return $textRole->processNode($parserContext, $fullRole, $part);
                    }

                    $inText = true;
                    break;
                case SpanLexer::WHITESPACE:
                    if (!$inText) {
                        // textrole names may not contain whitespace, we are not in a textrole
                        break 2;
                    }

                    $part .= $token->value;

                    break;
                default:
                    $part .= $token->value;
            }

            if ($lexer->moveNext() === false && $lexer->token === null) {
                break;
            }
        }

        $this->rollback($lexer, $initialPosition ?? 0);

        return null;
    }

    public function getPriority(): int
    {
        return 500;
    }
}
