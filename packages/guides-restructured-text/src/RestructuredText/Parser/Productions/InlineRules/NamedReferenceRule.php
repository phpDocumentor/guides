<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\InlineNode;
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
class NamedReferenceRule extends ReferenceRule
{
    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::NAMED_REFERENCE;
    }

    public function apply(BlockContext $blockContext, InlineLexer $lexer): InlineNode|null
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
