<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\AsciiSlugger;

class ImplicitHyperlinkTargetPassTest extends TestCase
{
    public function testAllImplicitUniqueSections(): void
    {
        $document = new DocumentNode('1', 'index');
        $expected = new DocumentNode('1', 'index');
        $slugger = new AsciiSlugger();
        foreach (['Document 1', 'Section A', 'Section B'] as $titles) {
            $document->addChildNode(
                new SectionNode(
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode($titles), 1, $slugger->slug($titles)->lower()->toString()),
                ),
            );
            $expected->addChildNode(
                new SectionNode(
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode($titles), 1, $slugger->slug($titles)->lower()->toString()),
                ),
            );
        }

        $pass = new ImplicitHyperlinkTargetPass();
        $resultDocuments = $pass->run([clone $document], new CompilerContext(new ProjectNode()));

        self::assertEquals([$expected], $resultDocuments);
    }

    public function testImplicitWithConflict(): void
    {
        $document = new DocumentNode('1', 'index');
        $expected = new DocumentNode('1', 'index');
        $slugger = new AsciiSlugger();

        foreach (['Document 1', 'Section A', 'Section A'] as $titles) {
            $document->addChildNode(
                new SectionNode(
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode($titles), 1, $slugger->slug($titles)->lower()->toString()),
                ),
            );
            $expected->addChildNode(
                new SectionNode(
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode($titles), 1, $slugger->slug($titles)->lower()->toString()),
                ),
            );
        }

        $pass = new ImplicitHyperlinkTargetPass();
        $resultDocuments = $pass->run([$document], new CompilerContext(new ProjectNode()));

        $section = $expected->getNodes()[2];
        self::assertInstanceOf(SectionNode::class, $section);
        $section->getTitle()->setId('section-a-1');

        self::assertEquals([$expected], $resultDocuments);
    }

    public function testExplicit(): void
    {
        $document = new DocumentNode('1', 'index');
        $expected = new DocumentNode('1', 'index');

        $document->addChildNode(new SectionNode(
            new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Document 1'), 1, 'document-1'),
        ));
        $expected->addChildNode(new SectionNode(
            new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Document 1'), 1, 'document-1'),
        ));

        $document->addChildNode(new AnchorNode('custom-anchor'));
        $expected->addChildNode(new AnchorNode('removed'));
        $expected = $expected->removeNode(1);

        $document->addChildNode(
            new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Section A'), 1, 'section-a')),
        );
        $expectedTitle = new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Section A'), 1, 'section-a');
        $expectedTitle->setId('custom-anchor');
        $expected->addChildNode(new SectionNode($expectedTitle));

        $pass = new ImplicitHyperlinkTargetPass();
        $resultDocuments = $pass->run([$document], new CompilerContext(new ProjectNode()));

        self::assertEquals([$expected], $resultDocuments);
    }

    public function testExplicitHasPriorityOverImplicit(): void
    {
        $document = new DocumentNode('1', 'index');
        $expected = new DocumentNode('1', 'index');

        $document->addChildNode(
            new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Document 1'), 1, 'document-1')),
        );
        $expectedTitle = new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Document 1'), 1, 'document-1');
        $expectedTitle->setId('document-1-1');
        $expected->addChildNode(new SectionNode($expectedTitle));

        $document->addChildNode(new AnchorNode('document-1'));
        $expected->addChildNode(new AnchorNode('removed'));
        $expected = $expected->removeNode(1);

        $document->addChildNode(
            new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Section A'), 1, 'section-a')),
        );
        $expectedTitle = new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Section A'), 1, 'section-a');
        $expectedTitle->setId('document-1');
        $expected->addChildNode(new SectionNode($expectedTitle));

        $pass = new ImplicitHyperlinkTargetPass();
        $resultDocuments = $pass->run([$document], new CompilerContext(new ProjectNode()));

        self::assertEquals([$expected], $resultDocuments);
    }
}
