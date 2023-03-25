<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

use phpDocumentor\Guides\Span\LiteralToken;
use PHPUnit\Framework\TestCase;

final class LiteralRoleRuleTest extends TestCase
{
    private LiteralRoleRule $rule;

    protected function setUp(): void
    {
        $this->rule = new LiteralRoleRule();
    }

    public function testApplies(): void
    {
        $tokens = new TokenIterator([
            '``text'
        ]);

        self::assertTrue($this->rule->applies($tokens));
    }

    /** @dataProvider literalProvider */
    public function testApply(string $input, string $literal): void
    {
        $tokens = new TokenIterator(explode(' ', $input));
        $expected = new LiteralToken('??', $literal);

        self::assertTrue($this->rule->applies($tokens));
        self::assertEquals($expected, $this->rule->apply($tokens));
    }

    public function literalProvider()
    {
        return [
            [
                '``literal``',
                'literal',
            ],
            [
                '``literal with spaces``',
                'literal with spaces'
            ],
            [
                '``literal with `single backticks` inside``',
                'literal with `single backticks` inside'
            ]
        ];
    }

    public function testNotEnding(): void
    {
        $input = '``literal not ending';
        $tokens = new TokenIterator(explode(' ', $input));

        self::assertNull($this->rule->apply($tokens));
        self::assertEquals('``literal', $tokens->current());
    }
}
