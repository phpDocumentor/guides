<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use Monolog\Logger;
use phpDocumentor\Guides\Nodes\InlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\CitationInlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\DocReferenceNode;
use phpDocumentor\Guides\Nodes\InlineToken\EmphasisToken;
use phpDocumentor\Guides\Nodes\InlineToken\FootnoteInlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\HyperLinkNode;
use phpDocumentor\Guides\Nodes\InlineToken\LiteralToken;
use phpDocumentor\Guides\Nodes\InlineToken\NbspToken;
use phpDocumentor\Guides\Nodes\InlineToken\NewlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\PlainTextToken;
use phpDocumentor\Guides\Nodes\InlineToken\StrongEmphasisToken;
use phpDocumentor\Guides\Nodes\InlineToken\VariableInlineNode;
use phpDocumentor\Guides\ParserContext;
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
use phpDocumentor\Guides\RestructuredText\TextRoles\ReferenceTextRole;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class InlineTokenParserTest extends TestCase
{
    public Logger $logger;
    private ParserContext&MockObject $parserContext;
    private InlineTokenParser $inlineTokenParser;

    public function setUp(): void
    {
        $this->logger = new Logger('test');
        $this->parserContext = $this->createMock(ParserContext::class);
        $defaultTextRoleFactory = new DefaultTextRoleFactory(
            new GenericTextRole(),
            [
                new ReferenceTextRole($this->logger),
                new DocReferenceTextRole($this->logger),
            ],
        );
        $this->inlineTokenParser = new InlineTokenParser([
            new NamedReferenceRule(),
            new AnonymousReferenceRule(),
            new PlainTextRule(),
            new InternalReferenceRule(),
            new TextRoleRule($defaultTextRoleFactory),
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
    public function testString(string $content, InlineNode $expected): void
    {
        $result = $this->inlineTokenParser->parse($content, $this->parserContext);
        self::assertEquals($expected, $result);
    }

    /** @return array<string, array<string | InlineNode>> */
    public static function inlineNodeProvider(): array
    {
        return [
            'Empty String' => [
                '',
                new InlineNode([]),
            ],
            'Alphanumeric Char' => [
                'x',
                new InlineNode([new PlainTextToken('', 'x')]),
            ],
            'Special Char' => [
                '`',
                new InlineNode([new PlainTextToken('', '`')]),
            ],
            'Alphanumeric Chars' => [
                'Lorem Ipsum',
                new InlineNode([new PlainTextToken('', 'Lorem Ipsum')]),
            ],
            'Named Reference' => [
                'myref_',
                new InlineNode([new HyperLinkNode('', 'myref')]),
            ],
            'Named Reference in string' => [
                'abc: myref_ xyz',
                new InlineNode([
                    new PlainTextToken('', 'abc: '),
                    new HyperLinkNode('', 'myref'),
                    new PlainTextToken('', ' xyz'),
                ]),
            ],
            'Anonymous Reference' => [
                'myref__',
                new InlineNode([new HyperLinkNode('', 'myref')]),
            ],
            'Anonymous Reference in string' => [
                'abc: myref__ xyz',
                new InlineNode([
                    new PlainTextToken('', 'abc: '),
                    new HyperLinkNode('', 'myref'),
                    new PlainTextToken('', ' xyz'),
                ]),
            ],
            'Internal Reference' => [
                '_`myref`',
                new InlineNode([new HyperLinkNode('', 'myref')]),
            ],
            'Internal Reference in string' => [
                'abc: _`myref` xyz',
                new InlineNode([
                    new PlainTextToken('', 'abc: '),
                    new HyperLinkNode('', 'myref'),
                    new PlainTextToken('', ' xyz'),
                ]),
            ],
            'No Internal Reference' => [
                '_`myref',
                new InlineNode([new PlainTextToken('', '_`myref')]),
            ],
            'Textrole' => [
                ':doc:`path/to/document`',
                new InlineNode([new DocReferenceNode('', 'path/to/document')]),
            ],
            'Textrole in string' => [
                'abc: :doc:`path/to/document` xyz',
                new InlineNode([
                    new PlainTextToken('', 'abc: '),
                    new DocReferenceNode('', 'path/to/document'),
                    new PlainTextToken('', ' xyz'),
                ]),
            ],
            'Named Reference, Phrased' => [
                '`myref`_',
                new InlineNode([new HyperLinkNode('', 'myref', 'myref')]),
            ],
            'Named Reference, Phrased, With URL' => [
                '`myref<https://test.com>`_',
                new InlineNode([new HyperLinkNode('', 'myref', 'https://test.com')]),
            ],
            'Named Reference, Phrased, With URL not ended' => [
                '`myref<https://test.com`_',
                new InlineNode([new HyperLinkNode('', 'myref<https://test.com', 'myref<https://test.com')]),
            ],
            'Anonymous Reference, Phrased' => [
                '`myref`__',
                new InlineNode([new HyperLinkNode('', 'myref', 'myref')]),
            ],
            'Anonymous Reference, Phrased, With URL' => [
                '`myref<https://test.com>`__',
                new InlineNode([new HyperLinkNode('', 'myref', 'https://test.com')]),
            ],
            'Footnote' => [
                '[1]_',
                new InlineNode([new FootnoteInlineNode('', '1', '', 1)]),
            ],
            'Named Footnote' => [
                '[#f1]_',
                new InlineNode([new FootnoteInlineNode('', '#f1', '#f1', 0)]),
            ],
            'Footnote in text' => [
                'Please RTFM [#f1]_.',
                new InlineNode([
                    new PlainTextToken('', 'Please RTFM '),
                    new FootnoteInlineNode('', '#f1', '#f1', 0),
                    new PlainTextToken('', '.'),
                ]),
            ],
            'Citation' => [
                '[f1]_',
                new InlineNode([new CitationInlineNode('', 'f1', 'f1')]),
            ],
            'Literal' => [
                '``simple``',
                new InlineNode([new LiteralToken('', 'simple')]),
            ],
            'Literal complex' => [
                '``**nothing** is` interpreted in here``',
                new InlineNode([new LiteralToken('', '**nothing** is` interpreted in here')]),
            ],
            /*
            'Literal Not to eager' => [
                '``:doc:`lorem``` and ``:code:`what``` sit `amet <https://consectetur.org>`_',
                new InlineNode([
                    new LiteralToken('', ':doc:`lorem`'),
                    new PlainTextToken('', ' and '),
                    new LiteralToken('', ':code:`what`'),
                    new PlainTextToken('', ' sit '),
                    new HyperLinkNode('', 'amet', 'https://consectetur.org')
                ]),
            ],
            */
            'Literal not ended' => [
                '``end is missing',
                new InlineNode([new PlainTextToken('', '``end is missing')]),
            ],
            'Default Textrole' => [
                '`simple`',
                new InlineNode([new LiteralToken('', 'simple')]),
            ],
            'Hyperlink' => [
                'https://example.com',
                new InlineNode([new HyperLinkNode('', 'https://example.com', 'https://example.com')]),
            ],
            'Emphasis' => [
                '*emphasis*',
                new InlineNode([new EmphasisToken('', 'emphasis')]),
            ],
            'Strong' => [
                '**strong**',
                new InlineNode([new StrongEmphasisToken('', 'strong')]),
            ],
            'Variable' => [
                '|variable|',
                new InlineNode([new VariableInlineNode('variable')]),
            ],
            'Escape' => [
                "\\x\\`\\ \\\n",
                new InlineNode([new PlainTextToken('', 'x`'), new NbspToken(''), new NewlineNode()]),
            ],
        ];
    }
}
