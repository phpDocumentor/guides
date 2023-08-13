<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use Monolog\Logger;
use phpDocumentor\Guides\Nodes\Inline\CitationInlineNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\EmphasisInlineNode;
use phpDocumentor\Guides\Nodes\Inline\FootnoteInlineNode;
use phpDocumentor\Guides\Nodes\Inline\GenericTextRoleInlineNode;
use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\Nodes\Inline\LiteralInlineNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\Inline\StrongInlineNode;
use phpDocumentor\Guides\Nodes\Inline\VariableInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\AnnotationRoleRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\AnonymousPhraseRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\AnonymousReferenceRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\DefaultTextRoleRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\EmphasisRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\EscapeRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\InternalReferenceRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\LiteralRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\NamedPhraseRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\NamedReferenceRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\PlainTextRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\StandaloneHyperlinkRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\StrongRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\TextRoleRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\VariableInlineRule;
use phpDocumentor\Guides\RestructuredText\TextRoles\DefaultTextRoleFactory;
use phpDocumentor\Guides\RestructuredText\TextRoles\DocReferenceTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\GenericTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\LiteralTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\ReferenceTextRole;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InlineTokenParserTest extends TestCase
{
    public Logger $logger;
    private DocumentParserContext $documentParserContext;
    private InlineParser $inlineTokenParser;
    private DefaultTextRoleFactory $textRoleFactory;

    public function setUp(): void
    {
        $this->logger = new Logger('test');
        $this->textRoleFactory = new DefaultTextRoleFactory(
            new GenericTextRole(),
            new LiteralTextRole(),
            [
                new ReferenceTextRole($this->logger),
                new DocReferenceTextRole($this->logger),
            ],
        );
        $this->documentParserContext = new DocumentParserContext(
            '',
            $this->createStub(ParserContext::class),
            $this->textRoleFactory,
            $this->createStub(MarkupLanguageParser::class),
        );
        $this->inlineTokenParser = new InlineParser([
            new NamedReferenceRule(),
            new AnonymousReferenceRule(),
            new PlainTextRule(),
            new InternalReferenceRule(),
            new TextRoleRule(),
            new NamedPhraseRule(),
            new AnonymousPhraseRule(),
            new AnnotationRoleRule(),
            new LiteralRule(),
            new DefaultTextRoleRule(),
            new StandaloneHyperlinkRule(),
            new EmphasisRule(),
            new StrongRule(),
            new VariableInlineRule(),
            new EscapeRule(),
        ]);
    }

    #[DataProvider('inlineNodeProvider')]
    public function testString(string $content, InlineCompoundNode $expected): void
    {
        $result = $this->inlineTokenParser->parse($content, new BlockContext($this->documentParserContext, ''));
        self::assertEquals($expected, $result);
    }

    /** @return array<string, array<string | InlineCompoundNode>> */
    public static function inlineNodeProvider(): array
    {
        return [
            'Empty String' => [
                '',
                new InlineCompoundNode([]),
            ],
            'Alphanumeric Char' => [
                'x',
                new InlineCompoundNode([new PlainTextInlineNode('x')]),
            ],
            'Special Char' => [
                '`',
                new InlineCompoundNode([new PlainTextInlineNode('`')]),
            ],
            'Alphanumeric Chars' => [
                'Lorem Ipsum',
                new InlineCompoundNode([new PlainTextInlineNode('Lorem Ipsum')]),
            ],
            'Named Reference' => [
                'myref_',
                new InlineCompoundNode([new HyperLinkNode('myref', 'myref')]),
            ],
            'Named Reference in string' => [
                'abc: myref_ xyz',
                new InlineCompoundNode([
                    new PlainTextInlineNode('abc: '),
                    new HyperLinkNode('myref', 'myref'),
                    new PlainTextInlineNode(' xyz'),
                ]),
            ],
            'Anonymous Reference' => [
                'myref__',
                new InlineCompoundNode([new HyperLinkNode('myref', 'myref')]),
            ],
            'Anonymous Reference in string' => [
                'abc: myref__ xyz',
                new InlineCompoundNode([
                    new PlainTextInlineNode('abc: '),
                    new HyperLinkNode('myref', 'myref'),
                    new PlainTextInlineNode(' xyz'),
                ]),
            ],
            'Internal Reference' => [
                '_`myref`',
                new InlineCompoundNode([new HyperLinkNode('myref', 'myref')]),
            ],
            'Internal Reference in string' => [
                'abc: _`myref` xyz',
                new InlineCompoundNode([
                    new PlainTextInlineNode('abc: '),
                    new HyperLinkNode('myref', 'myref'),
                    new PlainTextInlineNode(' xyz'),
                ]),
            ],
            'No Internal Reference' => [
                '_`myref',
                new InlineCompoundNode([new PlainTextInlineNode('_`myref')]),
            ],
            'Textrole' => [
                ':doc:`path/to/document`',
                new InlineCompoundNode([new DocReferenceNode('path/to/document')]),
            ],
            'Textrole in string' => [
                'abc: :doc:`path/to/document` xyz',
                new InlineCompoundNode([
                    new PlainTextInlineNode('abc: '),
                    new DocReferenceNode('path/to/document'),
                    new PlainTextInlineNode(' xyz'),
                ]),
            ],
            'Named Reference, Phrased' => [
                '`myref`_',
                new InlineCompoundNode([new HyperLinkNode('myref', 'myref')]),
            ],
            'Named Reference, Phrased, With URL' => [
                '`myref<https://test.com>`_',
                new InlineCompoundNode([new HyperLinkNode('myref', 'https://test.com')]),
            ],
            'Named Reference, Phrased, With URL not ended' => [
                '`myref<https://test.com`_',
                new InlineCompoundNode([new HyperLinkNode('myref<https://test.com', 'myref<https://test.com')]),
            ],
            'Anonymous Reference, Phrased' => [
                '`myref`__',
                new InlineCompoundNode([new HyperLinkNode('myref', 'myref')]),
            ],
            'Anonymous Reference, Phrased, With URL' => [
                '`myref<https://test.com>`__',
                new InlineCompoundNode([new HyperLinkNode('myref', 'https://test.com')]),
            ],
            'Footnote' => [
                '[1]_',
                new InlineCompoundNode([new FootnoteInlineNode('1', '', 1)]),
            ],
            'Named Footnote' => [
                '[#f1]_',
                new InlineCompoundNode([new FootnoteInlineNode('#f1', '#f1', 0)]),
            ],
            'Footnote in text' => [
                'Please RTFM [#f1]_.',
                new InlineCompoundNode([
                    new PlainTextInlineNode('Please RTFM '),
                    new FootnoteInlineNode('#f1', '#f1', 0),
                    new PlainTextInlineNode('.'),
                ]),
            ],
            'Citation' => [
                '[f1]_',
                new InlineCompoundNode([new CitationInlineNode('f1', 'f1')]),
            ],
            'Literal' => [
                '``simple``',
                new InlineCompoundNode([new LiteralInlineNode('simple')]),
            ],
            'Literal complex' => [
                '``**nothing** is` interpreted in here``',
                new InlineCompoundNode([new LiteralInlineNode('**nothing** is` interpreted in here')]),
            ],
            /*
            'Literal Not to eager' => [
                '``:doc:`lorem``` and ``:code:`what``` sit `amet <https://consectetur.org>`_',
                new InlineNode([
                    new LiteralToken(':doc:`lorem`'),
                    new PlainTextToken(' and '),
                    new LiteralToken(':code:`what`'),
                    new PlainTextToken(' sit '),
                    new HyperLinkNode('amet', 'https://consectetur.org')
                ]),
            ],
            */
            'Literal not ended' => [
                '``end is missing',
                new InlineCompoundNode([new PlainTextInlineNode('``end is missing')]),
            ],
            'Default Textrole' => [
                '`simple`',
                new InlineCompoundNode([new GenericTextRoleInlineNode('literal', 'simple')]),
            ],
            'Hyperlink' => [
                'https://example.com',
                new InlineCompoundNode([new HyperLinkNode('https://example.com', 'https://example.com')]),
            ],
            'Emphasis' => [
                '*emphasis*',
                new InlineCompoundNode([new EmphasisInlineNode('emphasis')]),
            ],
            'Strong' => [
                '**strong**',
                new InlineCompoundNode([new StrongInlineNode('strong')]),
            ],
            'Variable' => [
                '|variable|',
                new InlineCompoundNode([new VariableInlineNode('variable')]),
            ],
            'Escape' => [
                '\x\`\ \\n',
                new InlineCompoundNode([new PlainTextInlineNode('x`\\n')]),
            ],
        ];
    }
}
