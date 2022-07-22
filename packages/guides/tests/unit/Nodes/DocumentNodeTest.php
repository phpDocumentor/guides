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
}
