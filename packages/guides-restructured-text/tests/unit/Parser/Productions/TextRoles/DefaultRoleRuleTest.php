<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

use phpDocumentor\Guides\Span\LiteralToken;
use phpDocumentor\Guides\Span\ValueToken;

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
        ];
    }

    /**
     * @return array<int, array<int, string | ValueToken>>
     */
    public function expectedLiteralContentProvider() : array
    {
        return [
            [
                '`literal`',
                new LiteralToken('??', 'literal'),
            ],
            [
                '`literal with spaces`',
                new LiteralToken('??', 'literal with spaces'),
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
                '`literal not ending',
                '`literal',
            ],
        ];
    }
}
