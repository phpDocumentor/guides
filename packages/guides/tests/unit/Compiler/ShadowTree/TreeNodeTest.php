<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\ShadowTree;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;

final class TreeNodeTest extends TestCase
{
    private SectionNode $sectionNode1;
    private SectionNode $sectionNode2;
    private DocumentNode $documentNode;

    protected function setUp(): void
    {
        $this->sectionNode1 = new SectionNode(new TitleNode(new SpanNode('title1', []), 1, '1'));
        $this->sectionNode1->addChildNode(new RawNode('raw'));
        $this->sectionNode2 = new SectionNode(new TitleNode(new SpanNode('title2', []), 1, '2'));

        $this->documentNode = new DocumentNode('test', '/test');
        $this->documentNode->addChildNode($this->sectionNode1);
        $this->documentNode->addChildNode($this->sectionNode2);
    }

    public function testCreateFromDocumentNode(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);

        $this->assertSame($this->documentNode, $treeNode->getNode());
        $this->assertCount(2, $treeNode->getChildren());
        $this->assertSame($this->sectionNode1, $treeNode->getChildren()[0]->getNode());
        $this->assertSame($this->sectionNode2, $treeNode->getChildren()[1]->getNode());
        $this->assertSame($treeNode, $treeNode->getChildren()[0]->getParent());
        $this->assertSame($treeNode, $treeNode->getChildren()[1]->getParent());
    }

    public function testAddChild(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $sectionNode3 = new RawNode('raw');

        $treeNode->addChild($sectionNode3);

        $this->assertCount(3, $treeNode->getChildren());
        $this->assertSame($sectionNode3, $treeNode->getChildren()[2]->getNode());
        $this->assertSame($treeNode, $treeNode->getChildren()[2]->getParent());
    }

    public function testRemoveChild(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);

        $treeNode->removeChild($this->sectionNode1);

        $this->assertCount(1, $treeNode->getChildren());
        $this->assertSame($this->sectionNode2, $treeNode->getChildren()[0]->getNode());
        $this->assertSame($treeNode, $treeNode->getChildren()[0]->getParent());
    }

    public function testRemoveChildFromChild(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $nodeToRemove = $this->sectionNode1->getChildren()[0];

        $treeNode->getChildren()[0]->removeChild($nodeToRemove);

        $this->assertCount(0, $treeNode->getChildren()[0]->getChildren());
        $this->assertCount(0, $treeNode->getChildren()[0]->getNode()->getChildren());
        $this->assertSame($treeNode->getNode()->getChildren()[0], $treeNode->getChildren()[0]->getNode());
    }
}
