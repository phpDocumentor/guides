<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\AnnotationNode;
use phpDocumentor\Guides\Nodes\CitationNode;
use phpDocumentor\Guides\Nodes\FootnoteNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;
use PHPUnit\Framework\Attributes\DataProvider;

use function implode;

final class AnnotationRuleTest extends RuleTestCase
{
    private AnnotationRule $rule;

    public function setUp(): void
    {
        $this->rule = new AnnotationRule($this->givenInlineMarkupRule());
    }

    #[DataProvider('simpleAnnotationProvider')]
    public function testAnnotationApplies(string $input): void
    {
        $context = $this->createContext($input);
        self::assertTrue($this->rule->applies($context));
    }

    /** @return array<array<string>> */
    public static function simpleAnnotationProvider(): array
    {
        return [
            ['.. [name] Some Citation'],
            ['..  [name] Some Citation'],
            ['.. [#] Anonymous numbered footnote'],
            ['.. [1] Numbered footnote'],
            ['.. [42] Numbered footnote'],
            ['.. [007] Numbered footnote'],
            ['.. [#name] Named numbered footnote'],
        ];
    }

    #[DataProvider('simpleNonAnnotationProvider')]
    public function testAnnotationNotApplies(string $input): void
    {
        $context = $this->createContext($input);
        self::assertFalse($this->rule->applies($context));
    }

    /** @return array<array<string>> */
    public static function simpleNonAnnotationProvider(): array
    {
        return [
            [''],
            ['..[name]'],
            ['.. [name]'],
            ['.. [name]_ '],
        ];
    }

    #[DataProvider('annotationProvider')]
    public function testAnnotationNodeFromLinesIterator(
        string $input,
        AnnotationNode $node,
        string|null $nextLine = null,
        bool $nextLiteral = false,
    ): void {
        $documentParser = $this->createContext($input);

        self::assertTrue($this->rule->applies($documentParser));
        $result = $this->rule->apply($documentParser);
        self::assertEquals(
            $node,
            $result,
        );

        self::assertSame($nextLine, $documentParser->getDocumentIterator()->getNextLine());
        self::assertSame($nextLiteral, $documentParser->nextIndentedBlockShouldBeALiteralBlock);
    }

    /** @return array<string, array<string, string|AnnotationNode|null>> */
    public static function annotationProvider(): array
    {
        return [
            'single line citation' => [
                'input' => '..  [name] Some Citation',
                'output' => new CitationNode([new SpanNode('Some Citation', [])], 'name'),
            ],
            'single line Anonymous numbered footnote' => [
                'input' => '..  [#] Anonymous numbered footnote',
                'output' => new FootnoteNode([new SpanNode('Anonymous numbered footnote', [])], '#', 0),
            ],
            'single line Numbered footnote' => [
                'input' => '..  [42] Numbered footnote',
                'output' => new FootnoteNode([new SpanNode('Numbered footnote', [])], '', 42),
            ],
            'single line named footnote' => [
                'input' => '..  [#somename] Named footnote',
                'output' => new FootnoteNode([new SpanNode('Named footnote', [])], '#somename', 0),
            ],
            'multi line citation' => [
                'input' => <<<'RST'
..  [name] some multiline
    annotation
RST
                ,
                'output' => new CitationNode(
                    [
                        new SpanNode(
                            <<<'RST'
some multiline
annotation
RST,
                            [],
                        ),
                    ],
                    'name',
                ),
                'remaining' => null,
            ],
            'multi line citation, followed by paragraph' => [
                'input' => <<<'RST'
..  [name] some multiline
    annotation

This is a new paragraph
RST
                ,
                'output' => new CitationNode(
                    [
                        new SpanNode(
                            <<<'RST'
some multiline
annotation
RST,
                            [],
                        ),
                    ],
                    'name',
                ),
                'remaining' => 'This is a new paragraph',
            ],
            'multi line named footnote, followed by paragraph' => [
                'input' => <<<'RST'
..  [#name] some multiline
    annotation

This is a new paragraph
RST
                ,
                'output' => new FootnoteNode(
                    [
                        new SpanNode(
                            <<<'RST'
some multiline
annotation
RST,
                            [],
                        ),
                    ],
                    '#name',
                    0,
                ),
                'remaining' => 'This is a new paragraph',
            ],
            'multi line numbered footnote, followed by paragraph' => [
                'input' => <<<'RST'
..  [2023] some multiline
    annotation

This is a new paragraph
RST
                ,
                'output' => new FootnoteNode(
                    [
                        new SpanNode(
                            <<<'RST'
some multiline
annotation
RST,
                            [],
                        ),
                    ],
                    '',
                    2023,
                ),
                'remaining' => 'This is a new paragraph',
            ],
        ];
    }

    protected function givenInlineMarkupRule(): InlineMarkupRule
    {
        $spanParser = $this->createMock(SpanParser::class);
        $spanParser->method('parse')->willReturnCallback(
            static fn (array $arg): SpanNode => new SpanNode(implode("\n", $arg))
        );

        return new InlineMarkupRule($spanParser);
    }
}
