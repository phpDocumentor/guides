<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use Generator;
use phpDocumentor\Guides\Nodes\AnchorNode;
use PHPUnit\Framework\Attributes\DataProvider;

class LinkRuleTest extends RuleTestCase
{
    private LinkRule $rule;

    protected function setUp(): void
    {
        $this->rule = new LinkRule();
    }

    #[DataProvider('provideValidLinks')]
    public function testParseLink(
        string $line,
        string $expectedLabel,
        string $expectedUrl,
        AnchorNode|null $expectedNode,
    ): void {
        $context = $this->createContext($line);

        self::assertTrue($this->rule->applies($context));
        self::assertEquals($expectedNode, $this->rule->apply($context));

        self::assertSame([$expectedLabel => $expectedUrl], $context->getContext()->getLinks());
    }

    /** @return Generator<string, array{string, string, string, AnchorNode|null}> */
    public static function provideValidLinks(): Generator
    {
        yield 'Named link with quotes' => [
            '.. _`test`: https://example.com',
            'test',
            'https://example.com',
            null,
        ];

        yield 'Named link without quotes' => [
            '.. _test: https://example.com',
            'test',
            'https://example.com',
            null,
        ];

        yield 'Short anonymous link' => [
            '__ https://example.com',
            '',
            'https://example.com',
            null,
        ];

        yield 'Anchor link with quotes' => [
            '.. _`test`:',
            'test',
            '#test',
            new AnchorNode('test'),
        ];

        yield 'Anchor link without quotes' => [
            '.. _test:',
            'test',
            '#test',
            new AnchorNode('test'),
        ];
    }

    #[DataProvider('provideInvalidLinks')]
    public function testNotApplies(string $line): void
    {
        $context = $this->createContext($line);

        self::assertFalse($this->rule->applies($context));
    }

    /** @return Generator<string, array{string}> */
    public static function provideInvalidLinks(): Generator
    {
        yield 'Empty line' => [''];

        yield 'Not a link' => ['This is not a link'];

        yield 'Single dot' => ['. _test: https://example.com'];
    }
}
