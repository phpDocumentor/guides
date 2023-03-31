<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\Nodes\InlineToken\LiteralToken;
use phpDocumentor\Guides\Nodes\InlineToken\ValueToken;

final class DefaultRoleRuleTest extends StartEndRegexRoleRuleTest
{
    private DefaultRoleRule $rule;

    protected function setUp(): void
    {
        $this->rule = new DefaultRoleRule();
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
                ['`text'],
                true,
            ],
            [
                ['``text'],
                false,
            ],
            [
                ['\\`text'], // char is escaped
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
                '`text`',
                new LiteralToken('??', 'text'),
            ],
            [
                '`text with spaces`',
                new LiteralToken('??', 'text with spaces'),
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
                '`text not ending',
                '`text',
            ],
            [
                '`text not ending, char is escaped\\`',
                '`text',
            ],
        ];
    }
}
