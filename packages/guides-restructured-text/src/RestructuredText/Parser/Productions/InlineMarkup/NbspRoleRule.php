<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
use phpDocumentor\Guides\Nodes\InlineToken\NbspToken;

use function is_string;

class NbspRoleRule implements InlineMarkupRule
{
    public function applies(TokenIterator $tokens): bool
    {
        if (!is_string($tokens->current())) {
            return false;
        }

        return $tokens->current() === '~';
    }

    public function apply(TokenIterator $tokens): ?InlineMarkupToken
    {
        $tokens->snapShot();

        return new NbspToken('??');
    }
}
