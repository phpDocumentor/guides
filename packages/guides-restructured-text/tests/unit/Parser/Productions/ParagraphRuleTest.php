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

use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[UsesClass(LinesIterator::class)]
#[CoversMethod(ParagraphRule::class, '__construct')]
#[CoversMethod(ParagraphRule::class, 'apply')]
#[CoversMethod(ParagraphRule::class, 'applies')]
#[CoversMethod(ParagraphRule::class, 'isWhiteline')]
final class ParagraphRuleTest extends RuleTestCase
{
    #[DataProvider('paragraphProvider')]
    public function testParagraphNodeFromLinesIterator(
        string $input,
        ParagraphNode $output,
        string|null $remaining,
        bool $nextLiteral = false,
    ): void {
        $blockParser = $this->createContext($input);

        $markupRule = $this->givenInlineMarkupRule();

        $rule = new ParagraphRule($markupRule);

        self::assertTrue($rule->applies($blockParser));
        $result = $rule->apply($blockParser);
        self::assertEquals(
            $output,
            $result,
        );

        self::assertSame($remaining, $blockParser->getDocumentIterator()->getNextLine());
        self::assertSame($nextLiteral, $blockParser->getDocumentParserContext()->nextIndentedBlockShouldBeALiteralBlock);
    }

    /** @return mixed[][] */
    public static function paragraphProvider(): array
    {
        return [
            [
                'input' => 'some text.',
                'output' => new ParagraphNode([InlineCompoundNode::getPlainTextInlineNode('some text.')]),
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
                        InlineCompoundNode::getPlainTextInlineNode(<<<'RST'
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
                        InlineCompoundNode::getPlainTextInlineNode(<<<'RST'
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
                        InlineCompoundNode::getPlainTextInlineNode(
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
                        InlineCompoundNode::getPlainTextInlineNode(
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
                        InlineCompoundNode::getPlainTextInlineNode(
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
                        InlineCompoundNode::getPlainTextInlineNode(
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
                        InlineCompoundNode::getPlainTextInlineNode(
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
                        InlineCompoundNode::getPlainTextInlineNode(
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
