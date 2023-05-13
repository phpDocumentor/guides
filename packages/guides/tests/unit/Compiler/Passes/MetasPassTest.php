<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Meta\DocumentEntry;
use phpDocumentor\Guides\Meta\DocumentReferenceEntry;
use phpDocumentor\Guides\Meta\SectionEntry;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Nodes\TocNode;
use PHPUnit\Framework\TestCase;

final class MetasPassTest extends TestCase
{
    public function testDocumentTitlesAreCollectedAsTree(): void
    {
        $section = new SectionNode(new TitleNode(new SpanNode('index-title 1'), 1, 'index-title-1'));
        $section->addChildNode(new TocNode(['getting-started']));
        $section11 = new SectionNode(new TitleNode(new SpanNode('index-title 1.1'), 2, 'index-title-1-1'));
        $section->addChildNode($section11);

        $document = new DocumentNode(new ProjectNode(), '1', 'index');
        $document->addChildNode($section);

        $metas = new Metas([]);
        $pass = new MetasPass($metas);
        $pass->run([$document]);

        $entries = $metas->getAll();

        $expected = new DocumentEntry('index', new TitleNode(new SpanNode('index-title 1'), 1, 'index-title-1'));
        $s1 = new SectionEntry(new TitleNode(new SpanNode('index-title 1'), 1, 'index-title-1'));
        $s1->addChild(new DocumentReferenceEntry('getting-started'));
        $s1->addChild(new SectionEntry(new TitleNode(new SpanNode('index-title 1.1'), 2, 'index-title-1-1')));
        $expected->addChild($s1);

        self::assertEquals(
            ['index' => $expected],
            $entries,
        );
    }
}
