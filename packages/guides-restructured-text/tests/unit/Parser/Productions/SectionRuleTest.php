<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use Prophecy\PhpUnit\ProphecyTrait;
use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;
use phpDocumentor\Guides\UrlGeneratorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

final class SectionRuleTest extends TestCase
{
    use ProphecyTrait;
    public function testFirstTitleOpensSection(): void
    {
        $documentParser = $this->prophesize(DocumentParserContext::class)->reveal();
        $documentIterator = new LinesIterator();
        $documentIterator->load(<<<RST
#########
Title 1
#########
RST
        );

        $parserContext = new ParserContext(
            'foo',
            'test',
            1,
            $this->prophesize(FilesystemInterface::class)->reveal(),
            $this->prophesize(UrlGeneratorInterface::class)->reveal()
        );

        $documentParser = $this->prophesize(DocumentParserContext::class);
        $documentParser->getDocumentIterator()->willReturn($documentIterator);
        $documentParser->getContext()->willReturn($parserContext);
        $spanParser = $this->getSpanParser();

        $titleRule = new TitleRule(
            $spanParser->reveal()
        );

        $rule = new SectionRule($titleRule, []);

        $document = new DocumentNode('foo', 'index');

        $node = $rule->apply($documentParser->reveal(), $document);
        self::assertEquals(
            [new SectionNode(new TitleNode(new SpanNode('Title 1'), 1))],
            $document->getNodes()
        );
    }

    public function testSecondLevelTitleOpensChildSection(): void
    {
        $documentIterator = new LinesIterator();
        $documentIterator->load(<<<RST
#########
Title 1
#########

Title 1.1
=========
RST
        );

        $parserContext = new ParserContext(
            'foo',
            'test',
            1,
            $this->prophesize(FilesystemInterface::class)->reveal(),
            $this->prophesize(UrlGeneratorInterface::class)->reveal()
        );

        $documentParser = $this->prophesize(DocumentParserContext::class);
        $documentParser->getDocumentIterator()->willReturn($documentIterator);
        $documentParser->getContext()->willReturn($parserContext);
        $spanParser = $this->getSpanParser();

        $titleRule = new TitleRule(
            $spanParser->reveal()
        );

        $rule = new SectionRule($titleRule, []);

        $document = new DocumentNode('foo', 'index');

        $result = $rule->apply($documentParser->reveal(), $document);

        $section = new SectionNode(new TitleNode(new SpanNode('Title 1'), 1));
        $section->addNode(new SectionNode(new TitleNode(new SpanNode('Title 1.1'), 2)));

        self::assertEquals(
            [$section],
            $document->getNodes()
        );
    }

    public function testSameLevelSectionsAreAddedAtTheSameLevel(): void
    {
        $documentIterator = new LinesIterator();
        $documentIterator->load(<<<RST
#########
Title 1
#########

Title 1.1
=========

Title 1.2
=========
RST
        );

        $parserContext = new ParserContext(
            'foo',
            'test',
            1,
            $this->prophesize(FilesystemInterface::class)->reveal(),
            $this->prophesize(UrlGeneratorInterface::class)->reveal()
        );

        $documentParser = $this->prophesize(DocumentParserContext::class);
        $documentParser->getDocumentIterator()->willReturn($documentIterator);
        $documentParser->getContext()->willReturn($parserContext);
        $spanParser = $this->getSpanParser();

        $titleRule = new TitleRule(
            $spanParser->reveal()
        );

        $rule = new SectionRule($titleRule, []);

        $document = new DocumentNode('foo', 'index');

        $rule->apply($documentParser->reveal(), $document);

        $section = new SectionNode(new TitleNode(new SpanNode('Title 1'), 1));
        $section->addNode(new SectionNode(new TitleNode(new SpanNode('Title 1.1'), 2)));
        $section->addNode(new SectionNode(new TitleNode(new SpanNode('Title 1.2'), 2)));

        self::assertEquals(
            [$section],
            $document->getNodes()
        );
    }

    public function testSameLevelSectionsAreAddedAtTheSameLevel2(): void
    {
        $documentIterator = new LinesIterator();
        $documentIterator->load(<<<RST
#########
Title 1
#########

Title 1.1
=========

Title 1.1.1
^^^^^^^^^^^

Title 1.2
=========

#########
Title 2
#########

#########
Title 3
#########

RST
        );

        $parserContext = new ParserContext(
            'foo',
            'test',
            1,
            $this->prophesize(FilesystemInterface::class)->reveal(),
            $this->prophesize(UrlGeneratorInterface::class)->reveal()
        );

        $documentParser = $this->prophesize(DocumentParserContext::class);
        $documentParser->getDocumentIterator()->willReturn($documentIterator);
        $documentParser->getContext()->willReturn($parserContext);
        $spanParser = $this->getSpanParser();

        $titleRule = new TitleRule(
            $spanParser->reveal()
        );

        $rule = new SectionRule($titleRule, []);

        $document = new DocumentNode('foo', 'index');

        $rule->apply($documentParser->reveal(), $document);

        $section = new SectionNode(new TitleNode(new SpanNode('Title 1'), 1));
        $subSection = new SectionNode(new TitleNode(new SpanNode('Title 1.1'), 2));
        $section->addNode($subSection);
        $subSection->addNode(new SectionNode(new TitleNode(new SpanNode('Title 1.1.1'), 3)));
        $section->addNode(new SectionNode(new TitleNode(new SpanNode('Title 1.2'), 2)));
        $section2 = new SectionNode(new TitleNode(new SpanNode('Title 2'), 1));
        $section3 = new SectionNode(new TitleNode(new SpanNode('Title 3'), 1));

        self::assertEquals(
            [$section, $section2, $section3],
            $document->getNodes()
        );
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
