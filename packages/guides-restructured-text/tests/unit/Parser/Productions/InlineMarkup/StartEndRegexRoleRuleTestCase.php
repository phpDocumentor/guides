<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\Nodes\InlineToken\ValueToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function explode;
use function sprintf;
use function var_export;

abstract class StartEndRegexRoleRuleTestCase extends TestCase
{
    /** @param string[] $tokenStrings */
    #[DataProvider('ruleAppliesProvider')]
    public function testApplies(array $tokenStrings, bool $expected): void
    {
        $tokens = new TokenIterator($tokenStrings);

        self::assertEquals(
            $expected,
            $this->getRule()->applies($tokens),
            sprintf(
                '%s does not apply with expected result "%s"',
                var_export($tokenStrings, true),
                var_export($expected, true),
            ),
        );
    }

    #[DataProvider('expectedLiteralContentProvider')]
    public function testApply(string $input, ValueToken $expectedToken): void
    {
        $tokens = new TokenIterator(explode(' ', $input));

        self::assertTrue($this->getRule()->applies($tokens));
        self::assertEquals($expectedToken, $this->getRule()->apply($tokens));
    }

    #[DataProvider('notEndingProvider')]
    public function testNotEnding(string $input, string $expected): void
    {
        $tokens = new TokenIterator(explode(' ', $input));

        self::assertNull($this->getRule()->apply($tokens));
        self::assertEquals($expected, $tokens->current());
    }

    abstract public function getRule(): StartEndRegexRoleRule;

    /** @return array<int, array<int, array<int, string> | bool>> */
    abstract public static function ruleAppliesProvider(): array;

    /** @return array<int, array<int, string | ValueToken>> */
    abstract public static function expectedLiteralContentProvider(): array;

    /** @return array<int, array<int, string>> */
    abstract public static function notEndingProvider(): array;
}
