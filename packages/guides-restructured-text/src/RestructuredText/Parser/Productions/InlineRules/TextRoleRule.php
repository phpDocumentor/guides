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

use function strlen;
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
        $lastEscapedToken = null;

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
                        $lexer->moveNext();

                        return $this->createTextRoleNode($blockContext, $role, $domain, $part, $rawPart);
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
                    $lastEscapedToken = null;

                    break;
                case InlineLexer::ESCAPED_SIGN:
                    $resolved = substr($token->value, 1);
                    $part .= $resolved;
                    // Resolve escaped backslash (\\) in rawPart: the author explicitly
                    // escaped a backslash to display a single one. Other escapes (\T, \*,
                    // etc.) are preserved raw for code contexts that need literal
                    // backslash-letter sequences (e.g., PHP namespaces).
                    $rawPart .= $resolved === '\\' ? $resolved : $token->value;

                    $lastEscapedToken = $inText ? $token->value : null;

                    break;
                default:
                    $part .= $token->value;
                    $rawPart .= $token->value;
                    $lastEscapedToken = null;
            }

            if ($lexer->moveNext() === false && $lexer->token === null) {
                break;
            }
        }

        // The lexer's \`` catchable pattern (3 chars) swallows the closing
        // backtick. Only that specific token (strlen 3) means the delimiter was
        // consumed; regular 2-char escapes like \T or \\ are genuinely
        // unterminated roles that must roll back.
        // The 3-char token \`` represents \` (escaped backtick) + ` (closing
        // delimiter). Undo the full token, then re-add just the escaped backtick.
        if ($inText && $role !== null && $lastEscapedToken !== null && strlen($lastEscapedToken) === 3) {
            $resolved = substr($lastEscapedToken, 1);
            $rawAppended = $resolved === '\\' ? $resolved : $lastEscapedToken;
            $part = substr($part, 0, -strlen($resolved));
            $rawPart = substr($rawPart, 0, -strlen($rawAppended));
            $part .= '`';
            $rawPart .= '`';

            return $this->createTextRoleNode($blockContext, $role, $domain, $part, $rawPart);
        }

        $this->rollback($lexer, $initialPosition ?? 0);

        return null;
    }

    public function getPriority(): int
    {
        return 500;
    }

    private function createTextRoleNode(
        BlockContext $blockContext,
        string $role,
        string|null $domain,
        string $part,
        string $rawPart,
    ): InlineNodeInterface {
        $textRole = $blockContext->getDocumentParserContext()->getTextRoleFactoryForDocument()->getTextRole($role, $domain);
        $fullRole = ($domain ? $domain . ':' : '') . $role;

        return $textRole->processNode($blockContext->getDocumentParserContext(), $fullRole, $part, $rawPart);
    }
}
