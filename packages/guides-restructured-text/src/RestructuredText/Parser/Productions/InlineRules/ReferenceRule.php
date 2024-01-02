<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;

use function filter_var;
use function preg_replace;
use function str_ends_with;
use function str_replace;
use function substr;
use function trim;

use const FILTER_VALIDATE_URL;

abstract class ReferenceRule extends AbstractInlineRule
{
    protected function createReference(BlockContext $blockContext, string $reference, string|null $text = null, bool $registerLink = true): AbstractLinkInlineNode
    {
        // the link may have a new line in it, so we need to strip it
        // before setting the link and adding a token to be replaced
        $reference = str_replace("\n", ' ', $reference);
        $reference = trim(preg_replace('/\s+/', ' ', $reference) ?? '');

        if (str_ends_with($reference, '.rst') && filter_var($reference, FILTER_VALIDATE_URL) === false) {
            $reference = substr($reference, 0, -4);

            return new DocReferenceNode($reference, $text ?? $reference);
        }

        if ($registerLink && $text !== null) {
            $blockContext->getDocumentParserContext()->setLink($text, $reference);
        }

        return new HyperLinkNode($text ?? $reference, $reference);
    }
}
