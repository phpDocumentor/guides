<?php

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\Span\TextRoleToken;
use phpDocumentor\Guides\Span\ValueToken;

class TextRoleRule extends StartEndRegexRoleRule
{
    private const START ='/^:([a-z0-9]+):`/';
    private const END ='/(?<![`\\\\])`{1}$/';

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
        $role = preg_replace_callback('/:([a-z0-9]+):`(.+)`/mUsi', function ($match): string {
            return $match[1];
        }, $content);
        /** @var string $content */
        $content = (string) preg_replace($this->getStartRegex(), '', $content);
        $content = (string) preg_replace($this->getEndRegex(), '', $content);
        return new TextRoleToken('??', (string) $role, $content);
    }
}
