<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\Nodes\InlineToken\LiteralToken;
use phpDocumentor\Guides\Nodes\InlineToken\ValueToken;

final class LiteralRoleRuleTest extends StartEndRegexRoleRuleTest
{
    private LiteralRoleRule $rule;

    protected function setUp(): void
    {
        $this->rule = new LiteralRoleRule();
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
                ['``text'],
                true,
            ],
            [
                ['`text'],
                false,
            ],
        ];
    }

    /**
     * @return array<int, array<int, string | ValueToken>>
     */
    public function expectedLiteralContentProvider() : array
    {
        return [
            [
                '``literal``',
                new LiteralToken('??', 'literal'),
            ],
            [
                '``literal with spaces``',
                new LiteralToken('??', 'literal with spaces'),
            ],
            [
                '``literal with `single backticks` inside``',
                new LiteralToken('??', 'literal with `single backticks` inside'),
            ],
            [
            '``literal with \`` escaped backticks``',
                new LiteralToken('??', 'literal with \`` escaped backticks'),
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
                '``literal not ending',
                '``literal',
            ],
            [
                '``literal not ending, char is escaped\\``',
                '``literal',
            ],
        ];
    }
}
