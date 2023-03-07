<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

final class LiteralBlockRuleTest extends AbstractRuleTest
{
    private LiteralBlockRule $literalBlockRule;

    protected function setUp(): void
    {
        $this->literalBlockRule = new LiteralBlockRule();
    }

    /** @dataProvider validInputProvider */
    public function testApplies(string $input): void
    {
        $context = $this->createContext($input);

        self::assertTrue($this->literalBlockRule->applies($context));
    }

    public function validInputProvider(): array
    {
        return [
            'single white line' => [
                <<<RST
::

   Test block
RST
            ],
            [
            <<<RST
::





   Test block
RST
            ],
        ];
    }

    public function testDoesNotApplyWhenWhitelineIsMissing(): void
    {
        $context = $this->createContext(<<<RST
::
  foo
RST
);

        self::assertFalse($this->literalBlockRule->applies($context));
    }
}
