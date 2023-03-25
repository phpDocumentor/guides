<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

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
}
