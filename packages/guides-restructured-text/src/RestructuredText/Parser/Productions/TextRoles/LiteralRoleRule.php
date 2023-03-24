<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

use phpDocumentor\Guides\Span\LiteralToken;
use phpDocumentor\Guides\Span\SpanToken;

final class LiteralRoleRule implements TextRoleRule
{
    public function applies(TokenIterator $tokens): bool
    {
        return str_starts_with($tokens->current(), '``');
    }

    public function apply(TokenIterator $tokens): ?SpanToken
    {
        $tokens->snapShot();
        $content = substr($tokens->current(), 2);
        if (str_ends_with($content, '``')) {
            return new LiteralToken('??', substr($content, 0, -2));
        }

        while ($tokens->getNext() !== null && str_ends_with($tokens->getNext(), '``') === false) {
            $tokens->next();
            $content .= ' ' . $tokens->current();
        }

        if ($tokens->getNext() === null) {
            $tokens->restore();
            return null;
        }

        $tokens->next();
        $content .= ' ' . $tokens->current();

        return new LiteralToken('??', substr($content, 0, -2));
    }
}
