<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

use function trim;

/**
 * Rule to parse for simple anonymous references
 *
 * Syntax example:
 *
 *     Example reference__
 *
 * @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#anonymous-hyperlinks
 */
class AnonymousReferenceRule extends ReferenceRule
{
    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::ANONYMOUSE_REFERENCE;
    }

    public function apply(DocumentParserContext $documentParserContext, InlineLexer $lexer): HyperLinkNode|null
    {
        $node = $this->createAnonymousReference(
            $documentParserContext,
            trim((string) $lexer->token?->value, '_'),
        );
        $lexer->moveNext();

        return $node;
    }

    private function createAnonymousReference(DocumentParserContext $documentParserContext, string $link): HyperLinkNode
    {
        $documentParserContext->getContext()->resetAnonymousStack();
        $node = $this->createReference($documentParserContext, $link, null, false);
        $documentParserContext->getContext()->pushAnonymous($link);

        return $node;
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
