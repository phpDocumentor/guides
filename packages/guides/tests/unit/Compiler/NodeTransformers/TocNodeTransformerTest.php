<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Meta\DocumentEntry;
use phpDocumentor\Guides\Meta\DocumentReferenceEntry;
use phpDocumentor\Guides\Meta\SectionEntry;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TableOfContents\Entry as TocEntry;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Nodes\TocNode;
use PHPUnit\Framework\TestCase;

final class TocNodeTransformerTest extends TestCase
{
    public function testSimpleFlatToc(): void
    {
        $metas = $this->givenMetas();
        $node = (new TocNode(['index', 'page2']))->withOptions(['maxdepth' => 1]);
        $transformer = new TocNodeTransformer($metas);

        $transformedNode = $transformer->enterNode($node);

        self::assertEquals(
            [
                new TocEntry(
                    'index',
                    new TitleNode(new SpanNode('Title 1', []), 1, 'title-1'),
                ),
                new TocEntry(
                    'page2',
                    new TitleNode(new SpanNode('Title 2', []), 1, 'title-2'),
                ),
            ],
            $transformedNode->getEntries()
        );
    }

    public function testTocWithChildNodes(): void
    {
        $metas = $this->givenMetas();
        $node = (new TocNode(['index', 'page2']))->withOptions(['maxdepth' => 2]);
        $transformer = new TocNodeTransformer($metas);

        $transformedNode = $transformer->enterNode($node);

        $entry = new TocEntry(
            'index',
            new TitleNode(new SpanNode('Title 1', []), 1, 'title-1'),
            [
                new TocEntry(
                    'index#title-1-1',
                    new TitleNode(new SpanNode('Title 1.1', []), 2, 'title-1-1')
                ),
                new TocEntry(
                    'index#title-1-2',
                    new TitleNode(new SpanNode('Title 1.2', []), 2, 'title-1-2'),
                )
            ]
        );

        self::assertEquals(
            [
                $entry,
                new TocEntry(
                    'page2',
                    new TitleNode(new SpanNode('Title 2', []), 1, 'title-2'),
                ),
            ],
            $transformedNode->getEntries()
        );
    }

    public function testTocWithDocumentReferences(): void
    {
        $metas = $this->givenMetas();
        $node = (new TocNode(['page3']))->withOptions(['maxdepth' => 3]);
        $transformer = new TocNodeTransformer($metas);

        $transformedNode = $transformer->enterNode($node);

        $entry = new TocEntry(
            'index',
            new TitleNode(new SpanNode('Title 1', []), 1, 'title-1'),
            [
                new TocEntry(
                    'index#title-1-1',
                    new TitleNode(new SpanNode('Title 1.1', []), 2, 'title-1-1')
                ),
                new TocEntry(
                    'index#title-1-2',
                    new TitleNode(new SpanNode('Title 1.2', []), 2, 'title-1-2'),
                )
            ]
        );

        self::assertEquals(
            [
                new TocEntry(
                    'page3',
                    new TitleNode(new SpanNode('Title 3', []), 1, 'title-3'),
                ),
                $entry
            ],
            $transformedNode->getEntries()
        );
    }

    private function givenMetas(): Metas
    {
        $indexDoc = new DocumentEntry('index');
        $section = new SectionEntry(new TitleNode(new SpanNode('Title 1', []), 1, 'title-1'));
        $subSection = new SectionEntry(new TitleNode(new SpanNode('Title 1.1', []), 2, 'title-1-1'));
        $section->addChild($subSection);
        $section->addChild(new SectionEntry(new TitleNode(new SpanNode('Title 1.2', []), 2, 'title-1-2')));
        $indexDoc->addChild($section);

        $page2 = new DocumentEntry('page2');
        $page2->addChild(new SectionEntry(new TitleNode(new SpanNode('Title 2', []), 1, 'title-2')));

        $page3 = new DocumentEntry('page3');
        $page3->addChild(new SectionEntry(new TitleNode(new SpanNode('Title 3', []), 1, 'title-3')));
        $page3->addChild(new DocumentReferenceEntry('index'));

        return new Metas(
            [
                'index' => $indexDoc,
                'page2' => $page2,
                'page3' => $page3,
            ]
        );
    }
}
