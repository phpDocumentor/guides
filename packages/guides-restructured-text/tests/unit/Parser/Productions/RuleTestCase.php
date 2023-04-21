<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;
use phpDocumentor\Guides\UrlGenerator;
use PHPUnit\Framework\TestCase;

use function implode;

abstract class RuleTestCase extends TestCase
{
    protected static function assertRemainingEquals(string $expected, LinesIterator $actual): void
    {
        $rest = '';
        $actual->next();

        while ($actual->valid()) {
            $rest .= $actual->current() . "\n";
            $actual->next();
        }

        self::assertEquals($expected, $rest);
    }

    protected function createContext(string $input): DocumentParserContext
    {
        return new DocumentParserContext(
            $input,
            new ParserContext(
                'test',
                'test',
                1,
                $this->createStub(FilesystemInterface::class),
                new UrlGenerator()
            ),
            $this->createStub(MarkupLanguageParser::class)
        );
    }

    protected function givenInlineMarkupRule(): InlineMarkupRule
    {
        $spanParser = $this->createMock(SpanParser::class);
        $spanParser->method('parse')->willReturnCallback(
            static fn (array $arg): SpanNode => new SpanNode(implode("\n", $arg))
        );

        return new InlineMarkupRule($spanParser);
    }

    protected function givenCollectAllRuleContainer(): RuleContainer
    {
        return new RuleContainer(new CollectAllRule());
    }
}
