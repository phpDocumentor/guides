<?php

namespace phpDocumentor\Guides\Compiler\Passes;

use PHPUnit\Framework\TestCase;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;

class ImplicitHyperlinkTargetPassTest extends TestCase
{
    public function testAllImplicitUniqueSections(): void
    {
        $document = new DocumentNode('1', 'index');
        $expected = new DocumentNode('1', 'index');
        foreach (['Document 1', 'Section A', 'Section B'] as $titles) {
            $document->addChildNode(new SectionNode(new TitleNode(new SpanNode($titles), 1)));
            $expected->addChildNode(new SectionNode(new TitleNode(new SpanNode($titles), 1)));
        }

        $pass = new ImplicitHyperlinkTargetPass();
        $resultDocuments = $pass->run([clone $document]);

        self::assertEquals([$expected], $resultDocuments);
    }

    public function testImplicitWithConflict(): void
    {
        $document = new DocumentNode('1', 'index');
        $expected = new DocumentNode('1', 'index');
        foreach (['Document 1', 'Section A', 'Section A'] as $titles) {
            $document->addChildNode(new SectionNode(new TitleNode(new SpanNode($titles), 1)));
            $expected->addChildNode(new SectionNode(new TitleNode(new SpanNode($titles), 1)));
        }

        $pass = new ImplicitHyperlinkTargetPass();
        $resultDocuments = $pass->run([$document]);

        $expected->getNodes()[2]->getTitle()->setId('section-a-1');

        self::assertEquals([$expected], $resultDocuments);
    }

    public function testExplicit(): void
    {
        $document = new DocumentNode('1', 'index');
        $expected = new DocumentNode('1', 'index');

        $document->addChildNode(new SectionNode(new TitleNode(new SpanNode('Document 1'), 1)));
        $expected->addChildNode(new SectionNode(new TitleNode(new SpanNode('Document 1'), 1)));

        $document->addChildNode(new AnchorNode('custom-anchor'));
        $expected->addChildNode('removed');
        $expected = $expected->removeNode(1);

        $document->addChildNode(new SectionNode(new TitleNode(new SpanNode('Section A'), 1)));
        $expectedTitle = new TitleNode(new SpanNode('Section A'), 1);
        $expectedTitle->setId('custom-anchor');
        $expected->addChildNode(new SectionNode($expectedTitle));

        $pass = new ImplicitHyperlinkTargetPass();
        $resultDocuments = $pass->run([$document]);

        self::assertEquals([$expected], $resultDocuments);
    }

    public function testExplicitHasPriorityOverImplicit(): void
    {
        $document = new DocumentNode('1', 'index');
        $expected = new DocumentNode('1', 'index');

        $document->addChildNode(new SectionNode(new TitleNode(new SpanNode('Document 1'), 1)));
        $expectedTitle = new TitleNode(new SpanNode('Document 1'), 1);
        $expectedTitle->setId('document-1-1');
        $expected->addChildNode(new SectionNode($expectedTitle));

        $document->addChildNode(new AnchorNode('document-1'));
        $expected->addChildNode('removed');
        $expected = $expected->removeNode(1);

        $document->addChildNode(new SectionNode(new TitleNode(new SpanNode('Section A'), 1)));
        $expectedTitle = new TitleNode(new SpanNode('Section A'), 1);
        $expectedTitle->setId('document-1');
        $expected->addChildNode(new SectionNode($expectedTitle));

        $pass = new ImplicitHyperlinkTargetPass();
        $resultDocuments = $pass->run([$document]);

        self::assertEquals([$expected], $resultDocuments);
    }
}
