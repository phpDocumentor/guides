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
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

abstract class AbstractRuleTest extends TestCase
{
    use ProphecyTrait;

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
                $this->prophesize(FilesystemInterface::class)->reveal(),
                new UrlGenerator()
            ),
            $this->prophesize(MarkupLanguageParser::class)->reveal()
        );
    }

    protected function givenInlineMarkupRule(): InlineMarkupRule
    {
        $spanParser = $this->prophesize(SpanParser::class);
        $spanParser->parse(
            Argument::any(),
            Argument::any()
        )->will(fn($args): SpanNode => new SpanNode(implode("\n", $args[0])));
        return new InlineMarkupRule($spanParser->reveal());
    }

    protected function givenCollectAllRuleContainer(): RuleContainer
    {
        return new RuleContainer(new CollectAllRule());
    }
}
