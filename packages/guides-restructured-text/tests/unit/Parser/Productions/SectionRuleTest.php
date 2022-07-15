<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;
use phpDocumentor\Guides\UrlGenerator;
use phpDocumentor\Guides\UrlGeneratorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

final class SectionRuleTest extends TestCase
{
    public function testFirstTitleOpensSection(): void
    {
        $this->markTestSkipped();
        $documentParser = $this->prophesize(DocumentParserContext::class)->reveal();
        $documentIterator = new LinesIterator();
        $documentIterator->load(<<<RST
#########
Title 1
#########
RST
        );

        $documentParser = $this->prophesize(DocumentParserContext::class);
        $documentParser->getDocumentIterator()->willReturn($documentIterator);
        $markupLanguageParser = $this->getMarkupLanguageParser();
        $spanParser = $this->getSpanParser();

        $titleRule = new TitleRule(
            $spanParser->reveal()
        );

        $rule = new SectionRule($titleRule, $documentParser->reveal(), []);

        $document = new DocumentNode('foo', 'index');

        $rule->apply($documentIterator, $document);
        self::assertEquals(
            [new SectionNode(new TitleNode(new SpanNode('Title 1'), 1))],
            $document->getNodes()
        );
    }

    public function testSecondLevelTitleOpensChildSection(): void
    {
        $this->markTestSkipped();
        $documentIterator = new LinesIterator();
        $documentIterator->load(<<<RST
#########
Title 1
#########

#########
Title 2
#########
RST
        );

        $documentParser = $this->prophesize(DocumentParserContext::class);
        $documentParser->getDocumentIterator()->willReturn($documentIterator);
        $markupLanguageParser = $this->getMarkupLanguageParser();
        $spanParser = $this->getSpanParser();

        $titleRule = new TitleRule(
            $spanParser->reveal()
        );

        $rule = new SectionRule($titleRule, $documentParser->reveal(), []);

        $document = new DocumentNode('foo', 'index');

        $result = $rule->apply($documentIterator, $document);

        $section = new SectionNode(
            new TitleNode(new SpanNode('Title 1'), 1)
        );

        self::assertEquals(
            [$section],
            $document->getNodes()
        );
        self::assertEquals(
            new SectionNode(new TitleNode(new SpanNode('Title 2'), 2)),
            $result
        );
    }

    private function getMarkupLanguageParser()
    {
        $markdownParser = $this->prophesize(MarkupLanguageParser::class);
        $markdownParser->getEnvironment()->willReturn(
            new ParserContext(
                'test',
                'test',
                1,
                $this->prophesize(FilesystemInterface::class)->reveal(),
                $this->prophesize(UrlGeneratorInterface::class)->reveal()
            )
        );
        return $markdownParser;
    }

    private function getSpanParser()
    {
        $spanParser = $this->prophesize(SpanParser::class);
        $spanParser->parse(
            Argument::any(),
            Argument::type(ParserContext::class)
        )->will(fn($args) => new SpanNode($args[0]));
        return $spanParser;
    }
}
