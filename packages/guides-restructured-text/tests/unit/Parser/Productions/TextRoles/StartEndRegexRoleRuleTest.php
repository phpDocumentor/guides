<?php

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

use phpDocumentor\Guides\Span\LiteralToken;
use PHPUnit\Framework\TestCase;

abstract class StartEndRegexRoleRuleTest extends TestCase
{
    /**
     * @param string[] $tokenStrings
     * @dataProvider ruleAppliesProvider
     */
    public function testApplies(array $tokenStrings, bool $expected): void
    {
        $tokens = new TokenIterator($tokenStrings);

        self::assertEquals(
            $expected,
            $this->getRule()->applies($tokens),
            sprintf(
                '%s does not apply with expected result "%s"',
                var_export($tokenStrings, true),
                var_export($expected, true)
            )
        );
    }

    /** @dataProvider expectedLiteralContentProvider  */
    public function testApply(string $input, string $literal): void
    {
        $tokens = new TokenIterator(explode(' ', $input));
        $expected = new LiteralToken('??', $literal);

        self::assertTrue($this->getRule()->applies($tokens));
        self::assertEquals($expected, $this->getRule()->apply($tokens));
    }

    /** @dataProvider notEndingProvider  */
    public function testNotEnding(string $input, string $expected): void
    {
        $tokens = new TokenIterator(explode(' ', $input));

        self::assertNull($this->getRule()->apply($tokens));
        self::assertEquals($expected, $tokens->current());
    }
    abstract public function getRule(): StartEndRegexRoleRule;

    /**
     * @return array<int, array<int, array<int, string> | bool>>
     */
    abstract public function ruleAppliesProvider(): array;


    /**
     * @return array<int, array<int, string>>
     */
    abstract public function expectedLiteralContentProvider(): array;

    /**
     * @return array<int, array<int, string>>
     */
    abstract public function notEndingProvider(): array;
}
