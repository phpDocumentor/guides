<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

use phpDocumentor\Guides\Span\LiteralToken;
use phpDocumentor\Guides\Span\ValueToken;

final class LiteralRoleRule extends StartEndRegexRoleRule
{
    private const START ='/^`{2}(?!`)/';
    private const END = '/(?<!`)`{2}$/';

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
        return new LiteralToken('??', $content);
    }
}
