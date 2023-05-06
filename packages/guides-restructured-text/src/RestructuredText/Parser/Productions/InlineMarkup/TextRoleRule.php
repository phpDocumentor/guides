<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\Nodes\InlineToken\GenericTextRoleToken;
use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleFactory;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleNotFoundException;

use function assert;
use function is_string;
use function preg_replace;
use function preg_replace_callback;

class TextRoleRule extends StartEndRegexRoleRule
{
    private const START = '/^:([a-z0-9]+):`/';
    private const END = '/(?<![`\\\\])`{1}$/';

    public function __construct(private readonly TextRoleFactory $textRoleFactory)
    {
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
        $role = (string) preg_replace_callback('/:([a-z0-9]+):`(.+)`/mUsi', static fn ($match): string => $match[1], $content);
        $content = (string) preg_replace($this->getStartRegex(), '', $content);
        assert(is_string($content));
        $content = (string) preg_replace($this->getEndRegex(), '', $content);
        try {
            $textRole = $this->textRoleFactory->getTextRole($role);

            return $textRole->processNode($content);
        } catch (TextRoleNotFoundException) {
            return new GenericTextRoleToken('??', (string) $role, $content);
        }
    }
}
