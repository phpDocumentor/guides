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

use function rtrim;

/**
 * Rule to parse for simple named references
 *
 * Syntax examples:
 *
 *     Sample reference_
 *
 * @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#hyperlink-references
 */
final class NamedReferenceRule extends ReferenceRule implements CachableInlineRule
{
    public function getToken(): int
    {
        return InlineLexer::NAMED_REFERENCE;
    }

    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::NAMED_REFERENCE;
    }

    public function apply(BlockContext $blockContext, InlineLexer $lexer): InlineNodeInterface|null
    {
        $value = rtrim($lexer->token?->value ?? '', '_');
        $node = $this->createReference($blockContext, $value);

        $lexer->moveNext();

        return $node;
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
