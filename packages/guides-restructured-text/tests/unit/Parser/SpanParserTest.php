<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use Faker\Factory;
use Faker\Generator;
use Monolog\Logger;
use phpDocumentor\Guides\Nodes\InlineToken\DocReferenceNode;
use phpDocumentor\Guides\Nodes\InlineToken\AnnotationInlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\CitationInlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\CrossReferenceNode;
use phpDocumentor\Guides\Nodes\InlineToken\FootnoteInlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
use phpDocumentor\Guides\Nodes\InlineToken\LiteralToken;
use phpDocumentor\Guides\Nodes\InlineToken\ReferenceNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;
use phpDocumentor\Guides\RestructuredText\TextRoles\DefaultTextRoleFactory;
use phpDocumentor\Guides\RestructuredText\TextRoles\DocReferenceTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\GenericTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\ReferenceTextRole;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function current;

final class SpanParserTest extends TestCase
{
    private DefaultTextRoleFactory $defaultTextRoleFactory;
    public Logger $logger;
    public SpanLexer $spanLexer;
    public Generator $faker;
    private ParserContext&MockObject $parserContext;
    private SpanParser $spanProcessor;

    public function setUp(): void
    {
        $this->faker = Factory::create();
        $this->logger = new Logger('test');
        $this->spanLexer = new SpanLexer();
        $this->defaultTextRoleFactory = new DefaultTextRoleFactory(
            $this->logger,
            new GenericTextRole(),
            [
                new ReferenceTextRole($this->spanLexer, $this->logger),
                new DocReferenceTextRole($this->spanLexer, $this->logger),
            ],
        );
        $this->parserContext = $this->createMock(ParserContext::class);
        $this->spanProcessor = new SpanParser($this->defaultTextRoleFactory);
    }

    public function testInlineLiteralsAreReplacedWithToken(): void
    {
        $result = $this->spanProcessor->parse(
            'This text is an example of ``inline literals``.',
            $this->parserContext,
        );
        $token = current($result->getTokens());

        self::assertStringNotContainsString('``inline literals``', $result->getValue());
        self::assertInstanceOf(LiteralToken::class, $token);
        self::assertEquals(LiteralToken::TYPE, $token->getType());
        self::assertEquals(
            ['type' => 'literal'],
            $token->getTokenData(),
        );
    }

    #[DataProvider('invalidNotationsProvider')]
    public function testIncompleteStructuresAreIgnored(string $input): void
    {
        $result = $this->spanProcessor->parse($input, $this->parserContext);

        self::assertSame($input, $result->getValue());
        self::assertCount(0, $result->getTokens());
    }

    /** @return array<string, string[]> */
    public static function invalidNotationsProvider(): array
    {
        return [
            'Literal start without end' => ['This text is an example of `` mis-used.'],
            'Backtick without end' => ['This text is an example of `  ` mis-used.'],
            'Interpreted text without end' => ['This text is an example of :role:`foo mis-used.'],
            'Text role with spaces' => ['This text is an example of :invalid role:`foo mis-used.'],
            'Just a colon in a text' => ['This text is an example of role: mis-used.'],
            'Line ending with a colon' => ['to create new Symfony applications:'],
            'Embedded url start outside context' => ['This text is an example of <a>'],
            'Just an text_with_underscores' => ['Just an text_with_underscores'],
            'Text with several colons' => ['This text contains: several: colons'],
            'Text with invalid footnote' => ['This contains [Some arbitary text]_'],
        ];
    }

    /**
     * The result of this method is rather odd. There seems to be something wrong with the inline link replacement.
     * I don't think we should support this, but the regex is not covered by tests right now.
     * So improving it will be hard.
     */
    public function testIncompleteStructureLikeUrlIsReplaced(): void
    {
        $result = $this->spanProcessor->parse(
            'This text is an example of role:`mis-used`.',
            $this->parserContext,
        );
        self::assertMatchesRegularExpression('#This text is an example of [a-z0-9]{40}\\.#', $result->getValue());
    }

    #[DataProvider('namedHyperlinkReferenceProvider')]
    public function testNamedHyperlinkReferencesAreReplaced(
        string $input,
        string $referenceId,
        string $text,
        string $url = '',
        bool $anonymous = false,
    ): void {
        if ($anonymous === true || $url === '') {
            $this->parserContext->expects(self::never())->method('setLink');
        } else {
            $this->parserContext->expects(self::once())->method('setLink');
        }

        $result = $this->spanProcessor->parse($input, $this->parserContext);
        $token = current($result->getTokens());

        self::assertInstanceOf(InlineMarkupToken::class, $token);
        self::assertEquals(InlineMarkupToken::TYPE_LINK, $token->getType());
        self::assertEquals(
            [
                'type' => InlineMarkupToken::TYPE_LINK,
                'url' => $url,
                'link' => $text,
            ],
            $token->getTokenData(),
        );
        self::assertMatchesRegularExpression($referenceId, $result->getValue());
    }

    /** @return array<int, array<int, bool|string>> */
    public static function namedHyperlinkReferenceProvider(): array
    {
        return [
            [
                'This text is an example of link_.',
                '#This text is an example of [a-z0-9]{40}\\.#',
                'link',
            ],
            [
                'This text is an example of link_',
                '#This text is an example of [a-z0-9]{40}#',
                'link',
            ],
            [
                'This text is an example of `Phrase Reference`_.',
                '#This text is an example of [a-z0-9]{40}\\.#',
                'Phrase Reference',
            ],
            [
                'This text is an example of `Phrase < Reference`_',
                '#This text is an example of [a-z0-9]{40}#',
                'Phrase < Reference',
            ],
            [
                <<<'TEXT'
This text is an example of `Phrase
                 Reference`_.
TEXT
,
                '#This text is an example of [a-z0-9]{40}#',
                'Phrase Reference',
            ],
            [
                'This is an example of `embedded urls <http://google.com>`_ in a text',
                '#This is an example of [a-z0-9]{40} in a text#',
                'embedded urls',
                'http://google.com',
            ],
            [
                'This is an example of `embedded urls alias <alias_>`_ in a text',
                '#This is an example of [a-z0-9]{40} in a text#',
                'embedded urls alias',
                'alias_',
            ],
            [
                'A more complex example `\__call() <https://www.php.net/language.oop5.overloading#object.call>`_.',
                '#A more complex example [a-z0-9]{40}\\.#',
                '__call()',
                'https://www.php.net/language.oop5.overloading#object.call',
            ],
            [
                '(`RFC-7807 <https://tools.ietf.org/html/rfc7807>`__).',
                '#\\([a-z0-9]{40}\\)\\.#',
                'RFC-7807',
                'https://tools.ietf.org/html/rfc7807',
                true,
            ],
        ];
    }

    #[DataProvider('AnonymousHyperlinksProvider')]
    public function testAnonymousHyperlinksAreReplacedWithToken(
        string $input,
        string $referenceId,
        string $text,
        string $url = '',
    ): void {
        $this->parserContext->expects(self::once())->method('pushAnonymous')->with($text);
        $this->testNamedHyperlinkReferencesAreReplaced($input, $referenceId, $text, $url);
    }

    /** @return string[][] */
    public static function AnonymousHyperlinksProvider(): array
    {
        return [
            [
                'This is an example of an link__',
                '#This is an example of an [a-z0-9]{40}#',
                'link',
            ],
        ];
    }

    public function testInlineInternalTargetsAreReplaced(): void
    {
        $result = $this->spanProcessor->parse('Some _`internal ref` in text.', $this->parserContext);
        $token = current($result->getTokens());

        self::assertStringNotContainsString('_`internal ref`', $result->getValue());
        self::assertInstanceOf(InlineMarkupToken::class, $token);
        self::assertEquals(InlineMarkupToken::TYPE_LINK, $token->getType());
        self::assertEquals(
            [
                'type' => InlineMarkupToken::TYPE_LINK,
                'url' => '',
                'link' => 'internal ref',
            ],
            $token->getTokenData(),
        );
    }

    #[DataProvider('annotationProvider')]
    public function testAnnotationsAreReplaced(
        string $input,
        string $outputDoesNotContain,
        string $expectedType,
        string $expectedName,
        int $expectedFootnoteNumber = 0,
    ): void {
        $result = $this->spanProcessor->parse($input, $this->parserContext);
        $token = current($result->getTokens());

        self::assertStringNotContainsString($outputDoesNotContain, $result->toString());
        self::assertInstanceOf(AnnotationInlineNode::class, $token);
        self::assertEquals($expectedType, $token->getType());
        self::assertEquals($expectedName, $token->getName());
        if (!($token instanceof FootnoteInlineNode)) {
            return;
        }

        self::assertEquals($expectedFootnoteNumber, $token->getNumber());
    }

    /** @return array<string, array<int, int|string>> */
    public static function annotationProvider(): array
    {
        return [
            'numbered footnote' => [
                'Please RTFM [1]_.',
                '[1]_',
                FootnoteInlineNode::TYPE,
                '',
                1,
            ],
            'named footnote' => [
                'Lorem ipsum [#f1]_ dolor sit',
                '[#f1]_',
                FootnoteInlineNode::TYPE,
                '#f1',
            ],
            'anonymous footnote' => [
                'Lorem ipsum [#]_ dolor sit',
                '[#]_',
                FootnoteInlineNode::TYPE,
                '#',
            ],
            'citation' => [
                'Lorem ipsum [Ref]_ dolor sit amet.',
                '[Ref]_',
                CitationInlineNode::TYPE,
                'Ref',
            ],
            'citation with digit' => [
                'Lorem ipsum [123Ref]_ dolor sit amet.',
                '[123Ref]_',
                CitationInlineNode::TYPE,
                '123Ref',
            ],
        ];
    }

    public function testEmailAddressesAreReplacedWithToken(): void
    {
        $email = $this->faker->email;

        $result = $this->spanProcessor->parse($email, $this->parserContext);
        $tokens = $result->getTokens();
        $token = current($tokens);

        self::assertInstanceOf(InlineMarkupToken::class, $token);
        self::assertStringNotContainsString($email, $result->getValue());
        self::assertCount(1, $tokens);
        self::assertSame(InlineMarkupToken::TYPE_LINK, $token->getType());
        self::assertSame(
            [
                'link' => $email,
                'url' => 'mailto:' . $email,
                'type' => InlineMarkupToken::TYPE_LINK,
            ],
            $token->getTokenData(),
        );
    }

    public function testInlineUrlsAreReplacedWithToken(): void
    {
        $url = $this->faker->url;

        $result = $this->spanProcessor->parse($url, $this->parserContext);
        $tokens = $result->getTokens();
        $token = current($tokens);

        self::assertInstanceOf(InlineMarkupToken::class, $token);
        self::assertStringNotContainsString($url, $result->getValue());
        self::assertCount(1, $tokens);
        self::assertSame(InlineMarkupToken::TYPE_LINK, $token->getType());
        self::assertSame(
            [
                'link' => $url,
                'url' => $url,
                'type' => InlineMarkupToken::TYPE_LINK,
            ],
            $token->getTokenData(),
        );
    }

    #[DataProvider('docReferenceProvider')]
    public function testDocReferenceIsParsedIntoDocReferenceNode(
        string $span,
        string $replaced,
        string $url,
        string|null $domain = null,
        string|null $anchor = null,
        string|null $text = null,
    ): void {
        $result = $this->spanProcessor->parse($span, $this->parserContext);
        $token = current($result->getTokens());

        self::assertStringNotContainsString($replaced, $result->getValue());
        self::assertInstanceOf(DocReferenceNode::class, $token);
        self::assertEquals($url, $token->getDocumentLink(), 'DocumentLinks are different');
        self::assertEquals($domain, $token->getDomain(), 'Domains are different');
        self::assertEquals($anchor, $token->getAnchor(), 'Anchors are different');
        self::assertEquals($text ?? $url, $token->getText());
    }

    /** @return array<string, array<string, string|null>> */
    public static function docReferenceProvider(): array
    {
        return [
            'doc role x' => [
                'span' => 'Some :doc:`x` in text.',
                'replaced' => ':doc:`x`',
                'url' => 'x',
            ],
            'doc role' => [
                'span' => 'Some :doc:`path/to/document` in text.',
                'replaced' => ':doc:`path/to/document`',
                'url' => 'path/to/document',
            ],
            'doc role absolute' => [
                'span' => 'Some :doc:`/absolute/path/to/document` in text.',
                'replaced' => ':doc:`/absolute/path/to/document`',
                'url' => '/absolute/path/to/document',
            ],
            'doc with domain' => [
                'span' => 'Some :doc:`mydomain:path/to/document` in text.',
                'replaced' => ':doc:`mydomain:path/to/document`',
                'url' => 'path/to/document',
                'domain' => 'mydomain',
            ],
            'doc with role and anchor' => [
                'span' => 'Some :doc:`foo/subdoc#anchor` in text.',
                'replaced' => ':doc:`foo/subdoc#anchor`',
                'url' => 'foo/subdoc',
                'domain' => null,
                'anchor' => 'anchor',
            ],
            'doc with domain, role and anchor' => [
                'span' => 'Some :doc:`mydomain:foo/subdoc#anchor` in text.',
                'replaced' => ':doc:`mydomain:foo/subdoc#anchor`',
                'url' => 'foo/subdoc',
                'domain' => 'mydomain',
                'anchor' => 'anchor',
            ],
            'doc role, anchor and custom text' => [
                'span' => 'Some :doc:`link <mydomain:foo/subdoc#anchor>` in text.',
                'replaced' => ':doc:`link <mydomain:oo/subdoc#anchor>`',
                'url' => 'foo/subdoc',
                'domain' => 'mydomain',
                'anchor' => 'anchor',
                'text' => 'link',
            ],
            'doc role, with double point in text' => [
                'span' => 'Some :doc:`text: sometext <mydomain:foo/subdoc#anchor>` in text.',
                'replaced' => ':doc:`text: sometext <mydomain:foo/subdoc#anchor>`',
                'url' => 'foo/subdoc',
                'domain' => 'mydomain',
                'anchor' => 'anchor',
                'text' => 'text: sometext',
            ],
        ];
    }

    #[DataProvider('referenceProvider')]
    public function testReferenceIsParsedIntoReferenceNode(
        string $span,
        string $replaced,
        string $referenceName,
        string|null $domain = null,
        string|null $text = null,
    ): void {
        $result = $this->spanProcessor->parse($span, $this->parserContext);
        $token = current($result->getTokens());

        self::assertStringNotContainsString($replaced, $result->getValue());
        self::assertInstanceOf(ReferenceNode::class, $token);
        self::assertEquals($referenceName, $token->getReferenceName(), 'Urls are different');
        self::assertEquals($domain, $token->getDomain(), 'Domains are different');
        self::assertEquals($text ?? $referenceName, $token->getText());
    }

    /** @return array<string, array<string, string|null>> */
    public static function referenceProvider(): array
    {
        return [
            'ref role x' => [
                'span' => 'Some :ref:`x` in text.',
                'replaced' => ':ref:`x`',
                'referenceName' => 'x',
            ],
            'ref role' => [
                'span' => 'Some :ref:`title ref` in text.',
                'replaced' => ':ref:`title ref`',
                'referenceName' => 'title ref',
            ],
            'ref role with domain' => [
                'span' => 'Some :ref:`mydomain:title ref` in text.',
                'replaced' => ':ref:`mydomain:title ref`',
                'referenceName' => 'title ref',
                'domain' => 'mydomain',
            ],
            'ref role with domain and custom text' => [
                'span' => 'Some :ref:`link <mydomain:something>` in text.',
                'replaced' => ':ref:`link <mydomain:something>`',
                'referenceName' => 'something',
                'domain' => 'mydomain',
                'text' => 'link',
            ],
            'ref role colon in text' => [
                'span' => ':ref:`Text: with colon <mydomain:something>` in text.',
                'replaced' => ':ref:`Text: with colon <mydomain:something>`',
                'referenceName' => 'something',
                'domain' => 'mydomain',
                'text' => 'Text: with colon',
            ],
        ];
    }

    #[DataProvider('textRoleProvider')]
    public function testTextRoleIsParsedIntoInlineMarkupToken(
        string $span,
        string $replaced,
        string $role,
    ): void {
        $result = $this->spanProcessor->parse($span, $this->parserContext);
        $token = current($result->getTokens());

        self::assertStringNotContainsString($replaced, $result->getValue());
        self::assertInstanceOf(InlineMarkupToken::class, $token);
        self::assertEquals($role, $token->getType());
    }

    /** @return array<string, array<string, string|null>> */
    public static function textRoleProvider(): array
    {
        return [
            'interpreted text with role' => [
                'span' => 'Some :doc:`title ref` in text.',
                'replaced' => ':doc:`title ref`',
                'role' => 'doc',
            ],
            'interpreted text with role, colon in text' => [
                'span' => 'See also: :doc:`title ref`.',
                'replaced' => ':doc:`title ref`',
                'role' => 'doc',
            ],
            'interpreted text with domain and role' => [
                'span' => 'Some :php:class:`title ref` in text.',
                'replaced' => ':php:class:`title ref`',
                'role' => 'php:class',
            ],
            'just a interpreted text with domain and role' => [
                'span' => ':php:class:`title ref`',
                'replaced' => ':php:class:`title ref`',
                'role' => 'php:class',
            ],
            'php method reference' => [
                'span' => ':php:method:`phpDocumentor\Descriptor\ClassDescriptor::getParent()`',
                'replaced' => ':php:method:`phpDocumentor\Descriptor\ClassDescriptor::getParent()`',
                'role' => 'php:method',
            ],
        ];
    }

    public function testNoReplacementsAreDoneWhenNotNeeded(): void
    {
        $result = $this->spanProcessor->parse('Raw token', $this->parserContext);
        self::assertSame('Raw token', $result->getValue());
        self::assertEmpty($result->getTokens());
    }
}
