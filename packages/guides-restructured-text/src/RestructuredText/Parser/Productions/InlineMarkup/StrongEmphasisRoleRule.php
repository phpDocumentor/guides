<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\Nodes\InlineToken\StrongEmphasisToken;
use phpDocumentor\Guides\Nodes\InlineToken\ValueToken;

class StrongEmphasisRoleRule extends StartEndRegexRoleRule
{
    private const START ='/^\*{2}(?!\*)/';
    private const END = '/(?<![\*\\\\])\*{2}$/';

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
        return new StrongEmphasisToken('??', $content);
    }
}
