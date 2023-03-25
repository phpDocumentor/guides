<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

class StrongEmphasisRoleRule extends StartEndRegexRoleRule
{
    private const START ='/^\*{2}(?!\*)/';
    private const END = '/(?<!\*)\*{2}$/';

    public function getStartRegex(): string
    {
        return self::START;
    }

    public function getEndRegex(): string
    {
        return self::END;
    }
}
