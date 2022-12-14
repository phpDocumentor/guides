<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Meta\EntryLegacy;
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
                    new TitleNode(new SpanNode('Title 1', []), 1),
                ),
                new TocEntry(
                    'page2',
                    new TitleNode(new SpanNode('Title 2', []), 1),
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
            new TitleNode(new SpanNode('Title 1', []), 1),
        );
        $entry->addChild(new TocEntry(
            'index',
            new TitleNode(new SpanNode('Title 1.1', []), 2)
        ));

        $entry->addChild(new TocEntry(
            'index',
            new TitleNode(new SpanNode('Title 1.2', []), 2),
        ));

        self::assertEquals(
            [
                $entry,
                new TocEntry(
                    'page2',
                    new TitleNode(new SpanNode('Title 2', []), 1),
                ),
            ],
            $transformedNode->getEntries()
        );
    }

    private function givenMetas(): Metas
    {
        $metas = new Metas(
            [
                'index' => new EntryLegacy(
                    'index',
                    new TitleNode(new SpanNode('Title 1', []), 1),
                    [
                        new TitleNode(new SpanNode('Title 1.1', []), 2),
                        new TitleNode(new SpanNode('Title 1.1.1', []), 3),
                        new TitleNode(new SpanNode('Title 1.2', []), 2),
                    ],
                    [],
                    [],
                    0
                ),
                'page2' => new EntryLegacy(
                    'page2',
                    new TitleNode(new SpanNode('Title 2', []), 1),
                    [],
                    [],
                    [],
                    0
                ),
                'page3' => new EntryLegacy(
                    'page3',
                    new TitleNode(new SpanNode('Title 3', []), 1),
                    [],
                    [],
                    [],
                    0
                )
            ]
        );
        return $metas;
    }
}
