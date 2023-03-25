<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

use phpDocumentor\Guides\Span\EmphasisToken;
use phpDocumentor\Guides\Span\StrongEmphasisToken;
use phpDocumentor\Guides\Span\ValueToken;

final class StrongEmphasisRoleRuleTest extends StartEndRegexRoleRuleTest
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

    /**
     * @return array<int, array<int, array<int, string> | bool>>
     */
    public function ruleAppliesProvider(): array
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


    /**
     * @return array<int, array<int, string | ValueToken>>
     */
    public function expectedLiteralContentProvider(): array
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

    /**
     * @return array<int, array<int, string>>
     */
    public function notEndingProvider(): array
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
