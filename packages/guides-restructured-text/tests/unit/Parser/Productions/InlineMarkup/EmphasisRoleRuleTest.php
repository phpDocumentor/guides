<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\Nodes\InlineToken\EmphasisToken;
use phpDocumentor\Guides\Nodes\InlineToken\ValueToken;

final class EmphasisRoleRuleTest extends StartEndRegexRoleRuleTest
{
    private EmphasisRoleRule $rule;

    protected function setUp(): void
    {
        $this->rule = new EmphasisRoleRule();
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
                ['*text'],
                true,
            ],
            [
                ['**text'],
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
                '*text*',
                new EmphasisToken('??', 'text'),
            ],
            [
                '*text with spaces*',
                new EmphasisToken('??', 'text with spaces'),
            ],
            [
                '*text with escaped \\* star*',
                new EmphasisToken('??', 'text with escaped \\* star'),
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
                '*text not ending',
                '*text',
            ],
            [
                '*text not ending, char is escaped\\*',
                '*text',
            ],
        ];
    }
}
