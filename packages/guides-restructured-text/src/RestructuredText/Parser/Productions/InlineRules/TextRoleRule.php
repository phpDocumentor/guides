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

use function substr;

/**
 * Rule to parse for text roles such as ``:ref:`something` `
 */
final class TextRoleRule extends AbstractInlineRule
{
    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::COLON;
    }

    public function apply(BlockContext $blockContext, InlineLexer $lexer): InlineNodeInterface|null
    {
        $domain = null;
        $role = null;
        $rawPart = $part = '';
        $inText = false;

        $initialPosition = $lexer->token?->position;
        $lexer->moveNext();
        while ($lexer->token !== null) {
            $token = $lexer->token;
            switch ($token->type) {
                case $token->type === InlineLexer::COLON && $inText === false:
                    if ($role !== null) {
                        $domain = $role;
                        $role = $part;
                        $rawPart = $part = '';
                        break;
                    }

                    $role = $part;
                    $rawPart = $part = '';
                    break;
                case InlineLexer::BACKTICK:
                    if ($role === null) {
                        break 2;
                    }

                    if ($inText) {
                        $textRole = $blockContext->getDocumentParserContext()->getTextRoleFactoryForDocument()->getTextRole($role, $domain);
                        $fullRole = ($domain ? $domain . ':' : '') . $role;
                        $lexer->moveNext();

                        return $textRole->processNode($blockContext->getDocumentParserContext(), $fullRole, $part, $rawPart);
                    }

                    $inText = true;
                    break;
                case InlineLexer::WHITESPACE:
                    if (!$inText) {
                        // textrole names may not contain whitespace, we are not in a textrole
                        break 2;
                    }

                    $part .= $token->value;
                    $rawPart .= $token->value;

                    break;
                case InlineLexer::ESCAPED_SIGN:
                    $part .= substr($token->value, 1);
                    $rawPart .= $token->value;

                    break;
                default:
                    $part .= $token->value;
                    $rawPart .= $token->value;
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
