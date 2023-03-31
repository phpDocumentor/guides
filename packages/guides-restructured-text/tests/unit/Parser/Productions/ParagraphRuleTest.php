<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use Prophecy\PhpUnit\ProphecyTrait;

final class ParagraphRuleTest extends AbstractRuleTest
{
    use ProphecyTrait;

    /**
     * @uses \phpDocumentor\Guides\RestructuredText\Parser\LinesIterator
     *
     * @covers \phpDocumentor\Guides\RestructuredText\Parser\Productions\ParagraphRule::__construct
     * @covers \phpDocumentor\Guides\RestructuredText\Parser\Productions\ParagraphRule::apply
     * @covers \phpDocumentor\Guides\RestructuredText\Parser\Productions\ParagraphRule::applies
     * @covers \phpDocumentor\Guides\RestructuredText\Parser\Productions\ParagraphRule::isWhiteline
     * @dataProvider paragraphProvider
     */
    public function testParagraphNodeFromLinesIterator(
        string $input,
        ParagraphNode $node,
        ?string $nextLine,
        bool $nextLiteral = false
    ): void {
        $documentParser = $this->createContext($input);

        $markupRule = $this->givenInlineMarkupRule();

        $rule = new ParagraphRule($markupRule);

        self::assertTrue($rule->applies($documentParser));
        $result = $rule->apply($documentParser);
        self::assertEquals(
            $node,
            $result
        );

        self::assertSame($nextLine, $documentParser->getDocumentIterator()->getNextLine());
        self::assertSame($nextLiteral, $documentParser->nextIndentedBlockShouldBeALiteralBlock);
    }

    /** @return mixed[][] */
    public function paragraphProvider(): array
    {
        return [
            [
                'input' => 'some text.',
                'output' => new ParagraphNode([new SpanNode('some text.', [])]),
                'remaining' => null,
            ],
            [
                'input' => <<<RST
some multiline
paragraph
RST
,
                'output' => new ParagraphNode(
                    [
                        new SpanNode(
                            <<<RST
some multiline
paragraph
RST,
                            []
                        ),
                    ]
                ),
                'remaining' => null,
            ],
            [
                'input' => <<<RST
some multiline
paragraph

This is a new paragraph
RST
                ,
                'output' => new ParagraphNode(
                    [
                        new SpanNode(
                            <<<RST
some multiline
paragraph
RST,
                            []
                        ),
                    ]
                ),
                'remaining' => '',
            ],
            [
                'input' => <<<RST
some multiline
paragraph

This is a new paragraph
RST
                ,
                'output' => new ParagraphNode(
                    [
                        new SpanNode(
                            <<<RST
some multiline
paragraph
RST,
                            []
                        ),
                    ]
                ),
                'remaining' => '',
            ],
            [
                'input' => <<<RST
some multiline next paragraph is a literal block
paragraph::

  This is a new paragraph
RST
                ,
                'output' => new ParagraphNode(
                    [
                        new SpanNode(
                            <<<RST
some multiline next paragraph is a literal block
paragraph:
RST,
                            []
                        ),
                    ]
                ),
                'remaining' => '',
                'nextLiteral' => true,
            ],
            [
                'input' => <<<RST
some multiline next paragraph is a literal block
paragraph::

  This is a new paragraph
RST
                ,
                'output' => new ParagraphNode(
                    [
                        new SpanNode(
                            <<<RST
some multiline next paragraph is a literal block
paragraph:
RST,
                            []
                        ),
                    ]
                ),
                'remaining' => '',
                'nextLiteral' => true,
            ],
            [
                'input' => <<<RST
some multiline next paragraph is a literal block
paragraph: ::

  This is a new paragraph
RST
                ,
                'output' => new ParagraphNode(
                    [
                        new SpanNode(
                            <<<RST
some multiline next paragraph is a literal block
paragraph:
RST,
                            []
                        ),
                    ]
                ),
                'remaining' => '',
                'nextLiteral' => true,
            ],
            [
                'input' => <<<RST
some multiline next paragraph is a literal block
paragraph:

::

  This is a new paragraph
RST
            ,
                'output' => new ParagraphNode(
                    [
                        new SpanNode(
                            <<<RST
some multiline next paragraph is a literal block
paragraph:
RST,
                            []
                        ),
                    ]
                ),
                'remaining' => '',
                'nextLiteral' => false,
            ],
            [
                'input' => <<<RST
This is a top-level paragraph.

    This paragraph belongs to a first-level block quote.
RST
    ,
                'output' => new ParagraphNode(
                    [
                        new SpanNode(
                            <<<RST
This is a top-level paragraph.
RST,
                            []
                        ),
                    ]
                ),
                'remaining' => '',
                'nextLiteral' => false,
            ],
        ];
    }
}
