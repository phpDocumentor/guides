<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkup;

use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
use phpDocumentor\Guides\Nodes\InlineToken\NbspToken;
use PHPUnit\Framework\TestCase;

use function explode;
use function sprintf;
use function var_export;

abstract class NbspRoleRuleTest extends TestCase
{
    private NbspRoleRule $rule;

    protected function setUp(): void
    {
        $this->rule = new NbspRoleRule();
    }

    /**
     * @param string[] $tokenStrings
     *
     * @dataProvider ruleAppliesProvider
     */
    public function testApplies(array $tokenStrings, bool $expected): void
    {
        $tokens = new TokenIterator($tokenStrings);

        self::assertEquals(
            $expected,
            $this->rule->applies($tokens),
            sprintf(
                '%s does not apply with expected result "%s"',
                var_export($tokenStrings, true),
                var_export($expected, true)
            )
        );
    }

    /** @dataProvider expectedTokenProvider */
    public function testApply(string $input, InlineMarkupToken $expected): void
    {
        $tokens = new TokenIterator(explode(' ', $input));

        self::assertTrue($this->rule->applies($tokens));
        self::assertEquals($expected, $this->rule->apply($tokens));
    }

    /**
     * @return array<int, array<int, array<int, string> | bool>>
     */
    public function ruleAppliesProvider(): array
    {
        return [
            [
                ['~'],
                true,
            ],
            [
                ['~~'],
                false,
            ],
            [
                ['\~'],
                false,
            ],
        ];
    }

    /**
     * @return array<int, array<int, string | InlineMarkupToken>>
     */
    public function expectedTokenProvider(): array
    {
        return [
            [
                '~',
                new NbspToken('??'),
            ],
        ];
    }
}
