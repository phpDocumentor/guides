<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

use phpDocumentor\Guides\Span\LiteralToken;
use PHPUnit\Framework\TestCase;

final class StrongEmphasisRoleRuleTest extends TestCase
{
    private StrongEmphasisRoleRule $rule;

    protected function setUp(): void
    {
        $this->rule = new StrongEmphasisRoleRule();
    }

    public function testApplies(): void
    {
        $tokens = new TokenIterator([
            '**text'
        ]);

        self::assertTrue($this->rule->applies($tokens));
    }

    /** @dataProvider inputProvider */
    public function testApply(string $input, string $literal): void
    {
        $tokens = new TokenIterator(explode(' ', $input));
        $expected = new LiteralToken('??', $literal);

        self::assertTrue($this->rule->applies($tokens));
        self::assertEquals($expected, $this->rule->apply($tokens));
    }

    public function inputProvider()
    {
        return [
            [
                '**literal**',
                'literal',
            ],
            [
                '**literal with spaces**',
                'literal with spaces'
            ]
        ];
    }

    public function testNotEnding(): void
    {
        $input = '**literal not ending';
        $tokens = new TokenIterator(explode(' ', $input));

        self::assertNull($this->rule->apply($tokens));
        self::assertEquals('**literal', $tokens->current());
    }
}
