<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

use PHPUnit\Framework\TestCase;

final class DocumentNodeTest extends TestCase
{
    public function testGetTitleReturnsFirstSectionTitle(): void
    {
        $expected = new TitleNode(new SpanNode('Test'), 1);

        $document = new DocumentNode('test', 'file');
        $document->addNode(new SectionNode($expected));
        $document->addNode(new SectionNode(new TitleNode(new SpanNode('Test 2'), 1)));

        self::assertSame($expected, $document->getTitle());
    }

    public function testGetTitlesReturnsAllSectionTitles(): void
    {
        $title1 = new TitleNode(new SpanNode('Test'), 1);
        $subTitle = new TitleNode(new SpanNode('Test'), 1);
        $title2 = new TitleNode(new SpanNode('Test 2'), 1);

        $document = new DocumentNode('test', 'file');
        $section = new SectionNode($title1);
        $section->addNode(new SectionNode($subTitle));
        $document->addNode($section);
        $document->addNode(new SectionNode($title2));

        self::assertSame(
            [
                $title1,
                $subTitle,
                $title2
            ],
            $document->getTitles()
        );
    }

    public function testGetTocsReturnsAllSectionTocs(): void
    {
        $toc1 = new TocNode([]);
        $subToc = new TocNode([]);
        $toc2 = new TocNode([]);

        $document = new DocumentNode('test', 'file');
        $section = new SectionNode(new TitleNode(new SpanNode('Title'), 1));
        $section->addNode($toc1);
        $subSection = new SectionNode(new TitleNode(new SpanNode('Title'), 2));
        $subSection->addNode($subToc);
        $section->addNode($subSection);
        $document->addNode($section);
        $section2 = new SectionNode(new TitleNode(new SpanNode('Title'), 1));
        $section2->addNode($toc2);
        $document->addNode($section2);

        self::assertSame(
            [
                $toc1,
                $subToc,
                $toc2
            ],
            $document->getTocs()
        );
    }
}
