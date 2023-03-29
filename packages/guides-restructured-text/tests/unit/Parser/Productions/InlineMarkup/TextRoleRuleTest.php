<?php

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\RestructuredText\TextRoles\DefaultTextRoleFactory;
use phpDocumentor\Guides\Span\EmphasisToken;
use phpDocumentor\Guides\Span\LiteralToken;
use phpDocumentor\Guides\Span\NbspToken;
use phpDocumentor\Guides\Span\InlineMarkupToken;
use phpDocumentor\Guides\Span\StrongEmphasisToken;
use phpDocumentor\Guides\Span\GenericTextRoleToken;
use phpDocumentor\Guides\Span\ValueToken;
use PHPUnit\Framework\TestCase;

class TextRoleRuleTest extends StartEndRegexRoleRuleTest
{
    private TextRoleRule $rule;

    protected function setUp(): void
    {
        $this->rule = new TextRoleRule(new DefaultTextRoleFactory());
    }

    public function getRule(): StartEndRegexRoleRule
    {
        return $this->rule;
    }

    /**
     * @return array<int, array<int, array<int, string> | bool>>
     */
    public function ruleAppliesProvider(): array
    {
        return [
            [
                [':role:`something'],
                true,
            ],
            [
                [':php:class:`something'],
                false,
            ],
            [
                ['::role:`something'],
                false,
            ],
            [
                [':role\:`something'],
                false,
            ],
            [
                [':role:\`something'],
                false,
            ],
        ];
    }


    /**
     * @return array<int, array<int, string | ValueToken>>
     */
    public function expectedLiteralContentProvider(): array
    {
        return [
            [
                ':role:`something`',
                new GenericTextRoleToken('??', 'role', 'something'),
            ],
            [
                ':emphasis:`something`',
                new EmphasisToken('??', 'something'),
            ],
            [
                ':role:`something with spaces`',
                new GenericTextRoleToken('??', 'role', 'something with spaces'),
            ],
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function notEndingProvider(): array
    {
        return [
            [
                ':role:`something',
                ':role:`something',
            ],
            [
                ':role:`something\\`',
                ':role:`something\\`',
            ],
        ];
    }
}
