<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\DocumentNodeTraverser;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;

final class MoveAnchorTransformerTest extends TestCase
{
    private DocumentNodeTraverser $documentNodeTraverser;

    protected function setUp(): void
    {
        $this->documentNodeTraverser = new DocumentNodeTraverser(new class implements NodeTransformerFactory {
            /** @return iterable<MoveAnchorTransformer> */
            public function getTransformers(): iterable
            {
                //phpstan:ignore-next-line
                yield new MoveAnchorTransformer();
            }

            /** @return array<string, int> */
            public function getPriorities(): array
            {
                return [];
            }
        }, 30_000);
    }

    public function testAnchorNodeShouldBeMovedToNextSectionNodeWhenPositionedAboveSection(): void
    {
        $node = new AnchorNode('foo');
        $section = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('foo'), 1, 'id'));

        $document = new DocumentNode('123', 'some/path');
        $document->addChildNode($node);
        $document->addChildNode($section);

        $context = (new CompilerContext(new ProjectNode('test', 'test')))->withDocumentShadowTree($document);

        $this->documentNodeTraverser->traverse($document, $context);

        self::assertCount(1, $context->getDocumentNode()->getChildren());
        self::assertCount(2, $section->getChildren());
        self::assertSame($node, $section->getChildren()[0]);
    }

    public function testMultipleAnchorsShouldBeMovedToNextSectionNodeWhenPositionedAboveSection(): void
    {
        $node1 = new AnchorNode('foo');
        $node2 = new AnchorNode('bar');
        $node3 = new AnchorNode('bar2');
        $node4 = new AnchorNode('bar3');
        $section = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('foo'), 1, 'id'));

        $document = new DocumentNode('123', 'some/path');
        $document->addChildNode($node1);
        $document->addChildNode($node2);
        $document->addChildNode($node3);
        $document->addChildNode($node4);
        $document->addChildNode($section);

        $context = (new CompilerContext(new ProjectNode('test', 'test')))->withDocumentShadowTree($document);

        $this->documentNodeTraverser->traverse($document, $context);

        self::assertCount(1, $context->getDocumentNode()->getChildren());
        self::assertCount(5, $section->getChildren());
        self::assertEquals($node4, $section->getChildren()[0]);
        self::assertEquals($node3, $section->getChildren()[1]);
        self::assertEquals($node2, $section->getChildren()[2]);
        self::assertEquals($node1, $section->getChildren()[3]);
    }

    public function testAnchorShouldNotBeMovedTwice(): void
    {
        $node1 = new AnchorNode('foo');
        $sectionTitle = new TitleNode(InlineCompoundNode::getPlainTextInlineNode('foo'), 1, 'id');
        $section = new SectionNode($sectionTitle);
        $subSectionTitle = new TitleNode(InlineCompoundNode::getPlainTextInlineNode('sub foo'), 2, 'sub-id');
        $subSection = new SectionNode($subSectionTitle);
        $section->addChildNode(new AnchorNode('bar'));
        $section->addChildNode($subSection);

        $document = new DocumentNode('123', 'some/path');
        $document->addChildNode($node1);
        $document->addChildNode($section);

        $context = (new CompilerContext(new ProjectNode('test', 'test')))->withDocumentShadowTree($document);

        $this->documentNodeTraverser->traverse($document, $context);

        self::assertCount(1, $context->getDocumentNode()->getChildren());
        $updatedSection = $context->getDocumentNode()->getChildren()[0];
        self::assertInstanceOf(SectionNode::class, $updatedSection);
        self::assertEquals([$node1, $sectionTitle, $subSection], $updatedSection->getChildren());
        $updatedSubSection = $updatedSection->getChildren()[2];
        self::assertInstanceOf(SectionNode::class, $updatedSubSection);
        self::assertEquals([new AnchorNode('bar'), $subSectionTitle], $updatedSubSection->getChildren());
    }

    public function testNoMoveWhenAnchorIsOnlyChild(): void
    {
        $node = new AnchorNode('foo');

        $document = new DocumentNode('123', 'some/path');
        $document->addChildNode($node);

        $context = (new CompilerContext(new ProjectNode('test', 'test')))->withDocumentShadowTree($document);

        $this->documentNodeTraverser->traverse($document, $context);

        self::assertCount(1, $context->getDocumentNode()->getChildren());
        self::assertSame($node, $context->getDocumentNode()->getChildren()[0]);
    }

    public function testMoveAnchorsAtTheEndOfSectionToNextSection(): void
    {
        $node1 = new AnchorNode('foo');
        $node2 = new AnchorNode('bar');
        $section1 = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('foo'), 1, 'id'));
        $section1->addChildNode($node1);
        $section1->addChildNode($node2);

        $section2 = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('foo'), 1, 'id'));

        $document = new DocumentNode('123', 'some/path');
        $document->addChildNode($section1);
        $document->addChildNode($section2);

        $context = (new CompilerContext(new ProjectNode('test', 'test')))->withDocumentShadowTree($document);

        $this->documentNodeTraverser->traverse($document, $context);

        self::assertCount(2, $context->getDocumentNode()->getChildren());
        [$firstChild, $secondChild] = $context->getDocumentNode()->getChildren();
        self::assertInstanceOf(SectionNode::class, $firstChild);
        self::assertInstanceOf(SectionNode::class, $secondChild);
        self::assertCount(1, $firstChild->getChildren());
        self::assertCount(3, $secondChild->getChildren());
    }

    public function testMoveAnchorsAtTheEndOfSectionToNextParentNeighbourSection(): void
    {
        $node1 = new AnchorNode('foo');
        $node2 = new AnchorNode('bar');
        $section1 = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('foo'), 1, 'id'));
        $subSection = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('foo'), 2, 'id'));
        $subSection->addChildNode($node1);
        $subSection->addChildNode($node2);
        $section1->addChildNode($subSection);

        $section2 = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('foo'), 1, 'id'));

        $document = new DocumentNode('123', 'some/path');
        $document->addChildNode($section1);
        $document->addChildNode($section2);

        $context = (new CompilerContext(new ProjectNode('test', 'test')))->withDocumentShadowTree($document);

        $this->documentNodeTraverser->traverse($document, $context);

        self::assertCount(2, $context->getDocumentNode()->getChildren());
        [$firstChild, $secondChild] = $context->getDocumentNode()->getChildren();
        self::assertInstanceOf(SectionNode::class, $firstChild);
        self::assertInstanceOf(SectionNode::class, $secondChild);
        self::assertCount(2, $firstChild->getChildren());
        self::assertCount(3, $secondChild->getChildren());
    }
}
