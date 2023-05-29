<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use Monolog\Logger;
use phpDocumentor\Guides\Nodes\InlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\HyperLinkNode;
use phpDocumentor\Guides\Nodes\InlineToken\PlainTextToken;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\NamedReferenceRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\PlainTextRule;
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
        $this->inlineTokenParser = new InlineTokenParser([
            new NamedReferenceRule(),
            new PlainTextRule(),
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
                new InlineNode([new HyperLinkNode('', 'myref', '')]),
            ],
            'Named Reference in string' => [
                'abc: myref_ xyz',
                new InlineNode([
                    new PlainTextToken('', 'abc: '),
                    new HyperLinkNode('', 'myref', ''),
                    new PlainTextToken('', ' xyz'),
                ]),
            ],
        ];
    }
}
