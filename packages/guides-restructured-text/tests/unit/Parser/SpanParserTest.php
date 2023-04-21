<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use Faker\Factory;
use Faker\Generator;
use phpDocumentor\Guides\Nodes\InlineToken\CrossReferenceNode;
use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
use phpDocumentor\Guides\Nodes\InlineToken\LiteralToken;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function current;

final class SpanParserTest extends TestCase
{
    public Generator $faker;
    private ParserContext & MockObject $parserContext;
    private SpanParser $spanProcessor;

    public function setUp(): void
    {
        $this->faker = Factory::create();
        $this->parserContext = $this->createMock(ParserContext::class);
        $this->spanProcessor = new SpanParser();
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
            'Just a colon in a text' => ['This text is an example of role: mis-used.'],
            'Line ending with a colon' => ['to create new Symfony applications:'],
            'Embedded url start outside context' => ['This text is an example of <a>'],
            'Just an text_with_underscores' => ['Just an text_with_underscores'],
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
        bool $anonymous = false
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
        string $url = ''
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

    public function testFootNoteReferencesAreReplaced(): void
    {
        $result = null;
        $token = null;
        $this->markTestSkipped('Footnotes are not supported yet');
//        $result = $this->spanProcessor->parse('Please RTFM [1]_.', $this->parserContext->reveal());
//        $token = current($result->getTokens());
//
//        self::assertStringNotContainsString('[1]_', $result->getValueString());
//        self::assertInstanceOf(SpanToken::class, $token);
//        self::assertEquals(SpanToken::TYPE_REFERENCE, $token->getType());
//        self::assertEquals(
//            [
//                'type' => SpanToken::TYPE_REFERENCE,
//                'url' => '_`internal ref`',
//                'link' => 'internal ref',
//            ],
//            $token->getTokenData()
//        );
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

    #[DataProvider('crossReferenceProvider')]
    public function testInterpretedTextIsParsedIntoCrossReferenceNode(
        string $span,
        string $replaced,
        string $url,
        string $role = 'ref',
        ?string $domain = null,
        ?string $anchor = null,
        ?string $text = null
    ): void {
        $result = $this->spanProcessor->parse($span, $this->parserContext);
        $token = current($result->getTokens());

        self::assertStringNotContainsString($replaced, $result->getValue());
        self::assertInstanceOf(CrossReferenceNode::class, $token);
        self::assertEquals($url, $token->getUrl());
        self::assertEquals($role, $token->getRole());
        self::assertEquals($domain, $token->getDomain());
        self::assertEquals($anchor, $token->getAnchor());
        self::assertEquals($text ?? $url, $token->getText());
    }

    /** @return array<string, array<string, string|null>> */
    public static function crossReferenceProvider(): array
    {
        return [
            'interpreted text without role' => [
                'span' => 'Some `title ref` in text.',
                'replaced' => '`title ref`',
                'url' => 'title ref',
            ],
            'interpreted text with role' => [
                'span' => 'Some :doc:`title ref` in text.',
                'replaced' => ':doc:`title ref`',
                'url' => 'title ref',
                'role' => 'doc',
            ],
            'interpreted text with role and anchor' => [
                'span' => 'Some :doc:`foo/subdoc#anchor` in text.',
                'replaced' => ':doc:`foo/subdoc#anchor`',
                'url' => 'foo/subdoc',
                'role' => 'doc',
                'domain' => null,
                'anchor' => 'anchor',
            ],
            'interpreted text with role, anchor and custom text' => [
                'span' => 'Some :doc:`link <foo/subdoc#anchor>` in text.',
                'replaced' => ':doc:`link <foo/subdoc#anchor>`',
                'url' => 'foo/subdoc',
                'role' => 'doc',
                'domain' => null,
                'anchor' => 'anchor',
                'text' => 'link',
            ],
            'interpreted text with domain and role' => [
                'span' => 'Some :php:class:`title ref` in text.',
                'replaced' => ':php:class:`title ref`',
                'url' => 'title ref',
                'role' => 'class',
                'domain' => 'php',
            ],
            'just a interpreted text with domain and role' => [
                'span' => ':php:class:`title ref`',
                'replaced' => ':php:class:`title ref`',
                'url' => 'title ref',
                'role' => 'class',
                'domain' => 'php',
            ],
            'php method reference' => [
                'span' => ':php:method:`phpDocumentor\Descriptor\ClassDescriptor::getParent()`',
                'replaced' => ':php:method:`phpDocumentor\Descriptor\ClassDescriptor::getParent()`',
                'url' => 'phpDocumentor\Descriptor\ClassDescriptor::getParent()',
                'role' => 'method',
                'domain' => 'php',
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
