<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\Nodes\InlineToken\StrongEmphasisToken;
use phpDocumentor\Guides\Nodes\InlineToken\ValueToken;

final class StrongEmphasisRoleRuleTest extends StartEndRegexRoleRuleTestCase
{
    private StrongEmphasisRoleRule $rule;

    protected function setUp(): void
    {
        $this->rule = new StrongEmphasisRoleRule();
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
                ['**text'],
                true,
            ],
            [
                ['*text'],
                false,
            ],
            [
                ['\**text'],
                false,
            ],
        ];
    }

    /** @return array<int, array<int, string | ValueToken>> */
    public static function expectedLiteralContentProvider(): array
    {
        return [
            [
                '**literal**',
                new StrongEmphasisToken('??', 'literal'),
            ],
            [
                '**literal with spaces**',
                new StrongEmphasisToken('??', 'literal with spaces'),
            ],
            [
                '**text with escaped \\** stars**',
                new StrongEmphasisToken('??', 'text with escaped \\** stars'),
            ],
        ];
    }

    /** @return array<int, array<int, string>> */
    public static function notEndingProvider(): array
    {
        return [
            [
                '**literal not ending',
                '**literal',
            ],
            [
                '**text not ending, char is escaped\\**',
                '**text',
            ],
        ];
    }
}
