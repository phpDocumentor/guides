<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;

use function is_string;
use function preg_match;

abstract class StartEndRegexRoleRule implements InlineMarkupRule
{
    abstract public function getEndRegex(): string;

    abstract public function getStartRegex(): string;

    abstract protected function createToken(string $content): InlineMarkupToken;

    public function applies(TokenIterator $tokens): bool
    {
        if (!is_string($tokens->current())) {
            return false;
        }

        return preg_match($this->getStartRegex(), $tokens->current()) === 1;
    }

    public function apply(TokenIterator $tokens): ?InlineMarkupToken
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
