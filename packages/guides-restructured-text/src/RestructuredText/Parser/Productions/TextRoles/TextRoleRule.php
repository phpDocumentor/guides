<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

use phpDocumentor\Guides\Span\SpanToken;

interface TextRoleRule
{
    public function applies(TokenIterator $tokens): bool;

    public function apply(TokenIterator $tokens): ?SpanToken;
}
