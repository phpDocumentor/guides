<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;

interface InlineMarkupRule
{
    public function applies(TokenIterator $tokens): bool;

    public function apply(TokenIterator $tokens): ?InlineMarkupToken;
}
