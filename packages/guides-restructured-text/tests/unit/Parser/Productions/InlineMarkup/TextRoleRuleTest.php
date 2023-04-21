<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\Nodes\InlineToken\EmphasisToken;
use phpDocumentor\Guides\Nodes\InlineToken\GenericTextRoleToken;
use phpDocumentor\Guides\Nodes\InlineToken\ValueToken;
use phpDocumentor\Guides\RestructuredText\TextRoles\DefaultTextRoleFactory;

class TextRoleRuleTest extends StartEndRegexRoleRuleTestCase
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

    /** @return array<int, array<int, array<int, string> | bool>> */
    public static function ruleAppliesProvider(): array
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

    /** @return array<int, array<int, string | ValueToken>> */
    public static function expectedLiteralContentProvider(): array
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

    /** @return array<int, array<int, string>> */
    public static function notEndingProvider(): array
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
