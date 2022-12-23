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
        $document->addChildNode(new SectionNode($expected));
        $document->addChildNode(new SectionNode(new TitleNode(new SpanNode('Test 2'), 1)));

        self::assertSame($expected, $document->getTitle());
    }

    public function testGetTitlesReturnsAllSectionTitles(): void
    {
        $title1 = new TitleNode(new SpanNode('Test'), 1);
        $subTitle = new TitleNode(new SpanNode('Test'), 1);
        $title2 = new TitleNode(new SpanNode('Test 2'), 1);

        $document = new DocumentNode('test', 'file');
        $section = new SectionNode($title1);
        $section->addChildNode(new SectionNode($subTitle));
        $document->addChildNode($section);
        $document->addChildNode(new SectionNode($title2));

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
        $section->addChildNode($toc1);
        $subSection = new SectionNode(new TitleNode(new SpanNode('Title'), 2));
        $subSection->addChildNode($subToc);
        $section->addChildNode($subSection);
        $document->addChildNode($section);
        $section2 = new SectionNode(new TitleNode(new SpanNode('Title'), 1));
        $section2->addChildNode($toc2);
        $document->addChildNode($section2);

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
