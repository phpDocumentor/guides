<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\ShadowTree;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;

final class TreeNodeTest extends TestCase
{
    public function testCreateFromDocumentNode(): void
    {
        $sectionNode1 = new SectionNode(new TitleNode(new SpanNode('title1', []), 1, '1'));
        $sectionNode2 = new SectionNode(new TitleNode(new SpanNode('title2', []), 1, '2'));

        $documentNode = new DocumentNode('test', '/test');
        $documentNode->addChildNode($sectionNode1);
        $documentNode->addChildNode($sectionNode2);

        $treeNode = TreeNode::createFromDocument($documentNode);

        $this->assertSame($documentNode, $treeNode->getNode());
        $this->assertCount(2, $treeNode->getChildren());
        $this->assertSame($sectionNode1, $treeNode->getChildren()[0]->getNode());
        $this->assertSame($sectionNode2, $treeNode->getChildren()[1]->getNode());
        $this->assertSame($treeNode, $treeNode->getChildren()[0]->getParent());
        $this->assertSame($treeNode, $treeNode->getChildren()[1]->getParent());
    }
}
