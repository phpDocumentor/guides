<?php

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleFactory;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleNotFoundException;
use phpDocumentor\Guides\Span\GenericTextRoleToken;
use phpDocumentor\Guides\Span\InlineMarkupToken;
use phpDocumentor\Guides\Span\ValueToken;

class TextRoleRule extends StartEndRegexRoleRule
{
    private TextRoleFactory $textRoleFactory;
    private const START ='/^:([a-z0-9]+):`/';
    private const END ='/(?<![`\\\\])`{1}$/';

    public function __construct(TextRoleFactory $textRoleFactory)
    {
        $this->textRoleFactory = $textRoleFactory;
    }


    public function getStartRegex(): string
    {
        return self::START;
    }

    public function getEndRegex(): string
    {
        return self::END;
    }

    protected function createToken(string $content): InlineMarkupToken
    {
        $role = (string)preg_replace_callback('/:([a-z0-9]+):`(.+)`/mUsi', function ($match): string {
            return $match[1];
        }, $content);
        /** @var string $content */
        $content = (string) preg_replace($this->getStartRegex(), '', $content);
        $content = (string) preg_replace($this->getEndRegex(), '', $content);
        try {
            $textRole = $this->textRoleFactory->getTextRole($role);
            return $textRole->processNode($content);
        } catch (TextRoleNotFoundException $exception) {
            return new GenericTextRoleToken('??', (string) $role, $content);
        }
    }
}
