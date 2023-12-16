<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\ShadowTree;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;

final class TreeNodeTest extends TestCase
{
    private SectionNode $sectionNode1;
    private SectionNode $sectionNode2;
    private DocumentNode $documentNode;

    protected function setUp(): void
    {
        $this->sectionNode1 = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('test1'), 1, '1'));
        $this->sectionNode1->addChildNode(new RawNode('raw'));
        $this->sectionNode2 = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('test 2'), 1, '2'));
        $this->sectionNode2->addChildNode(new RawNode('raw'));

        $this->documentNode = new DocumentNode('test', '/test');
        $this->documentNode->addChildNode($this->sectionNode1);
        $this->documentNode->addChildNode($this->sectionNode2);
    }

    public function testCreateFromDocumentNode(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);

        self::assertSame($this->documentNode, $treeNode->getNode());
        self::assertCount(2, $treeNode->getChildren());
        self::assertSame($this->sectionNode1, $treeNode->getChildren()[0]->getNode());
        self::assertSame($this->sectionNode2, $treeNode->getChildren()[1]->getNode());
        self::assertSame($treeNode, $treeNode->getChildren()[0]->getParent());
        self::assertSame($treeNode, $treeNode->getChildren()[1]->getParent());
        self::assertSame($treeNode->getNode(), $treeNode->getRoot()->getNode());
    }

    public function testAddChild(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $sectionNode3 = new RawNode('raw');

        $treeNode->addChild($sectionNode3);

        self::assertCount(3, $treeNode->getChildren());
        self::assertSame($sectionNode3, $treeNode->getChildren()[2]->getNode());
        self::assertSame($treeNode, $treeNode->getChildren()[2]->getParent());
        self::assertSame($treeNode->getNode(), $treeNode->getRoot()->getNode());
    }

    public function testRemoveChild(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);

        $treeNode->removeChild($this->sectionNode1);

        self::assertCount(1, $treeNode->getChildren());
        self::assertSame($this->sectionNode2, $treeNode->getChildren()[0]->getNode());
        self::assertSame($treeNode, $treeNode->getChildren()[0]->getParent());
        self::assertSame($treeNode->getNode(), $treeNode->getRoot()->getNode());
    }

    public function testRemoveChildFromChild(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $nodeToRemove = $this->sectionNode1->getChildren()[0];

        $treeNode->getChildren()[0]->removeChild($nodeToRemove);

        self::assertCount(1, $treeNode->getChildren()[0]->getChildren());
        self::assertInstanceOf(CompoundNode::class, $treeNode->getChildren()[0]->getNode());
        self::assertCount(1, $treeNode->getChildren()[0]->getNode()->getChildren());
        self::assertInstanceOf(CompoundNode::class, $treeNode->getNode());
        self::assertSame($treeNode->getNode()->getChildren()[0], $treeNode->getChildren()[0]->getNode());
        self::assertSame($treeNode->getNode(), $treeNode->getRoot()->getNode());
    }
}
