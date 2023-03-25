<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

use phpDocumentor\Guides\Span\LiteralToken;
use phpDocumentor\Guides\Span\SpanToken;

class StrongEmphasisRoleRule implements TextRoleRule
{
    private string $endToken = '**';
    private string $startToken = '**';

    public function applies(TokenIterator $tokens): bool
    {
        return str_starts_with($tokens->current(), $this->startToken);
    }

    public function apply(TokenIterator $tokens): ?SpanToken
    {
        $tokens->snapShot();
        $content = substr($tokens->current(), 1);
        if ($this->isEndToken($content)) {
            return $this->createToken($content);
        }

        while ($tokens->getNext() !== null && $this->isEndToken($tokens->getNext()) === false) {
            $tokens->next();
            $content .= ' ' . $tokens->current();
        }

        if ($tokens->getNext() === null) {
            $tokens->restore();
            return null;
        }

        $tokens->next();
        $content .= ' ' . $tokens->current();

        return $this->createToken($content);
    }

    private function isEndToken($content): bool
    {
        return str_ends_with($content, $this->endToken);
    }

    private function createToken(string $content): LiteralToken
    {
        return new LiteralToken('??', substr($content, 1, -2));
    }
}
