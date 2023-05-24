<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use PHPUnit\Framework\Attributes\DataProvider;

final class CommentRuleTest extends RuleTestCase
{
    private CommentRule $rule;

    public function setUp(): void
    {
        $this->rule = new CommentRule();
    }

    #[DataProvider('simpleCommentProvider')]
    public function testCommentApplies(string $input): void
    {
        $context = $this->createContext($input);
        self::assertTrue($this->rule->applies($context));
    }

    /** @return array<array<string>> */
    public static function simpleCommentProvider(): array
    {
        return [
            ['.. Testing comment'],
            ['..'],
        ];
    }
}
