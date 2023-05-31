<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\InlineNode;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use PHPUnit\Framework\Attributes\DataProvider;

final class ParagraphRuleTest extends RuleTestCase
{
    /**
     * @uses \phpDocumentor\Guides\RestructuredText\Parser\LinesIterator
     *
     * @covers \phpDocumentor\Guides\RestructuredText\Parser\Productions\ParagraphRule::__construct
     * @covers \phpDocumentor\Guides\RestructuredText\Parser\Productions\ParagraphRule::apply
     * @covers \phpDocumentor\Guides\RestructuredText\Parser\Productions\ParagraphRule::applies
     * @covers \phpDocumentor\Guides\RestructuredText\Parser\Productions\ParagraphRule::isWhiteline
     */
    #[DataProvider('paragraphProvider')]
    public function testParagraphNodeFromLinesIterator(
        string $input,
        ParagraphNode $node,
        string|null $nextLine,
        bool $nextLiteral = false,
    ): void {
        $documentParser = $this->createContext($input);

        $markupRule = $this->givenInlineMarkupRule();

        $rule = new ParagraphRule($markupRule);

        self::assertTrue($rule->applies($documentParser));
        $result = $rule->apply($documentParser);
        self::assertEquals(
            $node,
            $result,
        );

        self::assertSame($nextLine, $documentParser->getDocumentIterator()->getNextLine());
        self::assertSame($nextLiteral, $documentParser->nextIndentedBlockShouldBeALiteralBlock);
    }

    /** @return mixed[][] */
    public static function paragraphProvider(): array
    {
        return [
            [
                'input' => 'some text.',
                'output' => new ParagraphNode([InlineNode::getPlainTextInlineNode('some text.')]),
                'remaining' => null,
            ],
            [
                'input' => <<<'RST'
some multiline
paragraph
RST
,
                'output' => new ParagraphNode(
                    [
                        InlineNode::getPlainTextInlineNode(<<<'RST'
some multiline
paragraph
RST),
                    ],
                ),
                'remaining' => null,
            ],
            [
                'input' => <<<'RST'
some multiline
paragraph

This is a new paragraph
RST
                ,
                'output' => new ParagraphNode(
                    [
                        InlineNode::getPlainTextInlineNode(<<<'RST'
some multiline
paragraph
RST),
                    ],
                ),
                'remaining' => '',
            ],
            [
                'input' => <<<'RST'
some multiline
paragraph

This is a new paragraph
RST
                ,
                'output' => new ParagraphNode(
                    [
                        InlineNode::getPlainTextInlineNode(
                            <<<'RST'
some multiline
paragraph
RST,
                        ),
                    ],
                ),
                'remaining' => '',
            ],
            [
                'input' => <<<'RST'
some multiline next paragraph is a literal block
paragraph::

  This is a new paragraph
RST
                ,
                'output' => new ParagraphNode(
                    [
                        InlineNode::getPlainTextInlineNode(
                            <<<'RST'
some multiline next paragraph is a literal block
paragraph:
RST,
                        ),
                    ],
                ),
                'remaining' => '',
                'nextLiteral' => true,
            ],
            [
                'input' => <<<'RST'
some multiline next paragraph is a literal block
paragraph::

  This is a new paragraph
RST
                ,
                'output' => new ParagraphNode(
                    [
                        InlineNode::getPlainTextInlineNode(
                            <<<'RST'
some multiline next paragraph is a literal block
paragraph:
RST,
                        ),
                    ],
                ),
                'remaining' => '',
                'nextLiteral' => true,
            ],
            [
                'input' => <<<'RST'
some multiline next paragraph is a literal block
paragraph: ::

  This is a new paragraph
RST
                ,
                'output' => new ParagraphNode(
                    [
                        InlineNode::getPlainTextInlineNode(
                            <<<'RST'
some multiline next paragraph is a literal block
paragraph:
RST,
                        ),
                    ],
                ),
                'remaining' => '',
                'nextLiteral' => true,
            ],
            [
                'input' => <<<'RST'
some multiline next paragraph is a literal block
paragraph:

::

  This is a new paragraph
RST
            ,
                'output' => new ParagraphNode(
                    [
                        InlineNode::getPlainTextInlineNode(
                            <<<'RST'
some multiline next paragraph is a literal block
paragraph:
RST,
                        ),
                    ],
                ),
                'remaining' => '',
                'nextLiteral' => false,
            ],
            [
                'input' => <<<'RST'
This is a top-level paragraph.

    This paragraph belongs to a first-level block quote.
RST
    ,
                'output' => new ParagraphNode(
                    [
                        InlineNode::getPlainTextInlineNode(
                            <<<'RST'
This is a top-level paragraph.
RST,
                        ),
                    ],
                ),
                'remaining' => '',
                'nextLiteral' => false,
            ],
        ];
    }
}
