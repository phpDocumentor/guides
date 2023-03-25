<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

use phpDocumentor\Guides\Span\EmphasisToken;
use phpDocumentor\Guides\Span\ValueToken;

class EmphasisRoleRule extends StartEndRegexRoleRule
{
    private const START ='/^\*{1}(?!\*)/';
    private const END = '/(?<!\*)\*{1}$/';

    public function getStartRegex(): string
    {
        return self::START;
    }

    public function getEndRegex(): string
    {
        return self::END;
    }

    protected function createToken(string $content): ValueToken
    {
        $content = (string) preg_replace($this->getStartRegex(), '', $content);
        $content = (string) preg_replace($this->getEndRegex(), '', $content);
        return new EmphasisToken('??', $content);
    }
}
