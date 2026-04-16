<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use Generator;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\RestructuredText\Parser\InlineParser;
use PHPUnit\Framework\Attributes\DataProvider;

final class TitleRuleTest extends RuleTestCase
{
    private TitleRule $rule;

    protected function setUp(): void
    {
        $inlineParser = $this->createMock(InlineParser::class);
        $inlineParser->method('parse')->willReturnCallback(
            static fn (string $text): InlineCompoundNode => new InlineCompoundNode([new PlainTextInlineNode($text)]),
        );

        $this->rule = new TitleRule($inlineParser);
    }

    #[DataProvider('provideTitleLines')]
    public function testAppliesToRegularTitleUnderline(string $content): void
    {
        self::assertTrue($this->rule->applies($this->createContext($content)));
    }

    /** @return Generator<string, array{string}> */
    public static function provideTitleLines(): Generator
    {
        yield 'underline' => ["Title\n=====\n"];
        yield 'overline + underline' => ["=====\nTitle\n=====\n"];
        yield 'paragraph starting with three dots is not explicit markup' => ["... and so on\n=============\n"];
    }

    #[DataProvider('provideExplicitMarkupLines')]
    public function testDoesNotApplyToExplicitMarkupLineFollowedByUnderline(string $content): void
    {
        self::assertFalse($this->rule->applies($this->createContext($content)));
    }

    /** @return Generator<string, array{string}> */
    public static function provideExplicitMarkupLines(): Generator
    {
        yield 'anchor with single space' => [".. _foo:\n========\n"];
        yield 'anchor with double space' => ["..  _foo:\n========\n"];
        yield 'anchor with tab' => [".. \t_foo:\n========\n"];
        yield 'anchor above level-2 underline' => [".. _foo:\n--------\n"];
        yield 'phrase reference anchor' => [".. _`Foo Bar`:\n==============\n"];
        yield 'directive' => [".. note::\n=========\n"];
        yield 'comment' => [".. some comment\n===============\n"];
        yield 'lonely double dot' => ["..\n==\n"];
    }
}
