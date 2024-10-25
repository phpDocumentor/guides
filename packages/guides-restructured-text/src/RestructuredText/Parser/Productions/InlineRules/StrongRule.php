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
use phpDocumentor\Guides\Nodes\Inline\StrongInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

/**
 * Rule to parse for default text roles such as `something`
 */
final class StrongRule extends AbstractInlineRule
{
    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::STRONG_DELIMITER;
    }

    public function apply(BlockContext $blockContext, InlineLexer $lexer): InlineNodeInterface|null
    {
        $text = '';

        $initialPosition = $lexer->token?->position;
        $lexer->moveNext();

        while ($lexer->token !== null) {
            $token = $lexer->token;
            switch ($token->type) {
                case $token->type === InlineLexer::STRONG_DELIMITER:
                    if ($text === '') {
                        break 2;
                    }

                    $lexer->moveNext();

                    return new StrongInlineNode($text);

                default:
                    $text .= $token->value;
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
        return 200;
    }
}
