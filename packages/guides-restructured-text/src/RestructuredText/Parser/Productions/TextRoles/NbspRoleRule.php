<?php

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

use phpDocumentor\Guides\Span\NbspToken;
use phpDocumentor\Guides\Span\SpanToken;

class NbspRoleRule implements TextRoleRule
{
    public function applies(TokenIterator $tokens): bool
    {
        if (!is_string($tokens->current())) {
            return false;
        }
        return $tokens->current() === '~';
    }

    public function apply(TokenIterator $tokens): ?SpanToken
    {
        $tokens->snapShot();
        return new NbspToken('??');
    }
}
