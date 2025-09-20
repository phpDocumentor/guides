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

use phpDocumentor\Guides\Nodes\Inline\LiteralInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

use function strlen;
use function substr;

/**
 * Rule for literals such as ``something``
 */
final class LiteralRule extends AbstractInlineRule implements CachableInlineRule
{
    public function getToken(): int
    {
        return InlineLexer::LITERAL;
    }

    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::LITERAL;
    }

    public function apply(BlockContext $blockContext, InlineLexer $lexer): LiteralInlineNode
    {
        $literal = $lexer->token?->value ?? '';
        if (strlen($literal) > 4) {
            $literal = substr($literal, 2, strlen($literal) - 4);
        }

        $lexer->moveNext();

        return new LiteralInlineNode($literal);
    }

    public function getPriority(): int
    {
        // Should be executed first as any other rules within may not be interpreted
        return 10_000;
    }
}
