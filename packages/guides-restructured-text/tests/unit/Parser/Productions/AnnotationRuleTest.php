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

use phpDocumentor\Guides\Nodes\AnnotationListNode;
use phpDocumentor\Guides\Nodes\CitationNode;
use phpDocumentor\Guides\Nodes\FootnoteNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use PHPUnit\Framework\Attributes\DataProvider;

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
        AnnotationListNode $node,
        string|null $nextLine = null,
        bool $nextLiteral = false,
    ): void {
        $blockParser = $this->createContext($input);

        self::assertTrue($this->rule->applies($blockParser));
        $result = $this->rule->apply($blockParser);
        self::assertEquals(
            $node,
            $result,
        );

        self::assertSame($nextLine, $blockParser->getDocumentIterator()->getNextLine());
        self::assertSame($nextLiteral, $blockParser->getDocumentParserContext()->nextIndentedBlockShouldBeALiteralBlock);
    }

    /** @return array<string, array<string, string|AnnotationListNode|null>> */
    public static function annotationProvider(): array
    {
        return [
            'single line citation' => [
                'input' => '..  [name] Some Citation',
                'output' => new AnnotationListNode([new CitationNode([InlineCompoundNode::getPlainTextInlineNode('Some Citation')], 'name')], 'citation-list'),
            ],
            'single line Anonymous numbered footnote' => [
                'input' => '..  [#] Anonymous numbered footnote',
                'output' => new AnnotationListNode([new FootnoteNode([InlineCompoundNode::getPlainTextInlineNode('Anonymous numbered footnote')], '#', 0)], 'footer-list'),
            ],
            'single line Numbered footnote' => [
                'input' => '..  [42] Numbered footnote',
                'output' => new AnnotationListNode([new FootnoteNode([InlineCompoundNode::getPlainTextInlineNode('Numbered footnote')], '', 42)], 'footer-list'),
            ],
            'single line named footnote' => [
                'input' => '..  [#somename] Named footnote',
                'output' => new AnnotationListNode([new FootnoteNode([InlineCompoundNode::getPlainTextInlineNode('Named footnote')], '#somename', 0)], 'footer-list'),
            ],
            'multi line citation' => [
                'input' => <<<'RST'
..  [name] some multiline
    annotation
RST
                ,
                'output' => new AnnotationListNode([
                    new CitationNode(
                        [
                            InlineCompoundNode::getPlainTextInlineNode(
                                <<<'RST'
some multiline
annotation
RST,
                            ),
                        ],
                        'name',
                    ),
                ], 'citation-list'),
                'remaining' => null,
            ],
            'multi line citation, followed by paragraph' => [
                'input' => <<<'RST'
..  [name] some multiline
    annotation

This is a new paragraph
RST
                ,
                'output' => new AnnotationListNode([
                    new CitationNode(
                        [
                            InlineCompoundNode::getPlainTextInlineNode(
                                <<<'RST'
some multiline
annotation
RST,
                            ),
                        ],
                        'name',
                    ),
                ], 'citation-list'),
                'remaining' => 'This is a new paragraph',
            ],
            'multi line named footnote, followed by paragraph' => [
                'input' => <<<'RST'
..  [#name] some multiline
    annotation

This is a new paragraph
RST
                ,
                'output' => new AnnotationListNode([
                    new FootnoteNode(
                        [
                            InlineCompoundNode::getPlainTextInlineNode(
                                <<<'RST'
some multiline
annotation
RST,
                            ),
                        ],
                        '#name',
                        0,
                    ),
                ], 'footer-list'),
                'remaining' => 'This is a new paragraph',
            ],
            'multi line numbered footnote, followed by paragraph' => [
                'input' => <<<'RST'
..  [2023] some multiline
    annotation

This is a new paragraph
RST
                ,
                'output' => new AnnotationListNode([
                    new FootnoteNode(
                        [
                            InlineCompoundNode::getPlainTextInlineNode(
                                <<<'RST'
some multiline
annotation
RST,
                            ),
                        ],
                        '',
                        2023,
                    ),
                ], 'footer-list'),
                'remaining' => 'This is a new paragraph',
            ],
            'footnote list' => [
                'input' => <<<'RST'
..  [1] Footnote 1
..  [2] Footnote 2
..  [3] Footnote 3

This is a new paragraph
RST
                ,
                'output' => new AnnotationListNode([
                    new FootnoteNode(
                        [
                            InlineCompoundNode::getPlainTextInlineNode('Footnote 1'),
                        ],
                        '',
                        1,
                    ),
                    new FootnoteNode(
                        [
                            InlineCompoundNode::getPlainTextInlineNode('Footnote 2'),
                        ],
                        '',
                        2,
                    ),
                    new FootnoteNode(
                        [
                            InlineCompoundNode::getPlainTextInlineNode('Footnote 3'),
                        ],
                        '',
                        3,
                    ),
                ], 'footer-list'),
                'remaining' => 'This is a new paragraph',
            ],
        ];
    }
}
