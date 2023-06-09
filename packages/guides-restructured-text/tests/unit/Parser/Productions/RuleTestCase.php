<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Nodes\InlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\PlainTextToken;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineTokenParser;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\UrlGenerator;
use PHPUnit\Framework\TestCase;

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
                new ProjectNode(),
                'test',
                'test',
                1,
                $this->createStub(FilesystemInterface::class),
                new UrlGenerator(),
            ),
            $this->createStub(MarkupLanguageParser::class),
        );
    }

    protected function givenInlineMarkupRule(): InlineMarkupRule
    {
        $inlineTokenParser = $this->createMock(InlineTokenParser::class);
        $inlineTokenParser->method('parse')->willReturnCallback(
            static fn (string $arg): InlineNode => new InlineNode([
                new PlainTextToken($arg),
            ])
        );

        return new InlineMarkupRule($inlineTokenParser);
    }

    protected function givenCollectAllRuleContainer(): RuleContainer
    {
        return new RuleContainer(new CollectAllRule());
    }
}
