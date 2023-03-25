<?php

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

use phpDocumentor\Guides\Span\SpanToken;
use phpDocumentor\Guides\Span\ValueToken;

abstract class StartEndRegexRoleRule implements TextRoleRule
{
    abstract public function getEndRegex(): string;

    abstract public function getStartRegex(): string;

    abstract protected function createToken(string $content): ValueToken;

    public function applies(TokenIterator $tokens): bool
    {
        if (!is_string($tokens->current())) {
            return false;
        }
        return preg_match($this->getStartRegex(), $tokens->current()) === 1;
    }

    public function apply(TokenIterator $tokens): ?SpanToken
    {
        $tokens->snapShot();
        $content = $tokens->current();
        if (!is_string($content)) {
            return null;
        }
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

    private function isEndToken(string $content): bool
    {
        return (bool) preg_match($this->getEndRegex(), $content);
    }
}
