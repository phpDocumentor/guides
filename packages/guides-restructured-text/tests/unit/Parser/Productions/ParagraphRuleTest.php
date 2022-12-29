<?php

declare(strict_types=1);

namespace unit\Parser\Productions;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkupRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\ParagraphRule;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;
use phpDocumentor\Guides\UrlGenerator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

use function implode;

final class ParagraphRuleTest extends TestCase
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
        $iterator = new LinesIterator();
        $iterator->load($input);

        $documentParser = new DocumentParserContext(
            $input,
            new ParserContext(
                'test',
                'test',
                1,
                $this->prophesize(FilesystemInterface::class)->reveal(),
                new UrlGenerator()
            ),
            $this->prophesize(MarkupLanguageParser::class)->reveal()
        );

        $spanParser = $this->prophesize(SpanParser::class);
        $spanParser->parse(
            Argument::any(),
            Argument::any()
        )->will(fn($args) => new SpanNode(implode("\n", $args[0])));

        $rule = new ParagraphRule(
            new InlineMarkupRule($spanParser->reveal())
        );

        self::assertTrue($rule->applies($documentParser));
        $result = $rule->apply($documentParser);
        self::assertEquals(
            $node,
            $result
        );

        self::assertSame($nextLine, $documentParser->getDocumentIterator()->getNextLine());
        self::assertSame($nextLiteral, $documentParser->nextIndentedBlockShouldBeALiteralBlock);
    }

    public function paragraphProvider(): array
    {
        return [
            [
                'input' => 'some text.',
                'output' => new ParagraphNode(new SpanNode('some text.', [])),
                'remaining' => null,
            ],
            [
                'input' => <<<RST
some multiline
paragraph
RST
,
                'output' => new ParagraphNode(
                    new SpanNode(
                        <<<RST
some multiline
paragraph
RST,
                        []
                    )
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
                    new SpanNode(
                        <<<RST
some multiline
paragraph
RST,
                        []
                    )
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
                    new SpanNode(
                        <<<RST
some multiline
paragraph
RST,
                        []
                    )
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
                    new SpanNode(
                        <<<RST
some multiline next paragraph is a literal block
paragraph:
RST,
                        []
                    )
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
                    new SpanNode(
                        <<<RST
some multiline next paragraph is a literal block
paragraph:
RST,
                        []
                    )
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
                    new SpanNode(
                        <<<RST
some multiline next paragraph is a literal block
paragraph:
RST,
                        []
                    )
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
                    new SpanNode(
                        <<<RST
some multiline next paragraph is a literal block
paragraph:
RST,
                        []
                    )
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
                new SpanNode(
                    <<<RST
This is a top-level paragraph.
RST,
                    []
                )
            ),
        'remaining' => '',
        'nextLiteral' => false,
            ],
        ];
    }
}
