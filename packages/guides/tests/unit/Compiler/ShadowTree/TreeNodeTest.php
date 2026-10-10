<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\Compiler\ShadowTree;

use Error;
use LogicException;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DefinitionListNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionListItemNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;
use WeakReference;

use function array_keys;
use function count;
use function gc_disable;
use function gc_enable;

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

    public function testCreateFromDocumentWrapsNestedChildren(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);

        foreach ($treeNode->getChildren() as $section) {
            self::assertCount(2, $section->getChildren());
            $leaf = $section->getChildren()[1];
            self::assertInstanceOf(RawNode::class, $leaf->getNode());
            self::assertSame($section, $leaf->getParent());
            self::assertSame($treeNode, $leaf->getRoot());
        }
    }

    public function testRootHasNoParent(): void
    {
        self::assertNull(TreeNode::createFromDocument($this->documentNode)->getParent());
    }

    public function testAddChildUpdatesUnderlyingNode(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $raw = new RawNode('new');

        $treeNode->addChild($raw);

        self::assertSame($raw, $this->documentNode->getChildren()[2]);
    }

    public function testAddCompoundChildPropagatesRoot(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $section = $this->createSection('added');

        $treeNode->addChild($section);

        $added = $treeNode->getChildren()[2];
        self::assertSame($treeNode, $added->getRoot());
        self::assertSame($treeNode, $added->getChildren()[0]->getRoot());
        self::assertSame($added, $added->getChildren()[0]->getParent());
    }

    public function testAddChildToNonCompoundNodeThrows(): void
    {
        $leaf = TreeNode::createFromDocument($this->documentNode)->getChildren()[0]->getChildren()[1];

        $this->expectException(LogicException::class);
        $leaf->addChild(new RawNode('x'));
    }

    public function testPushChild(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $raw = new RawNode('pushed');

        $treeNode->pushChild($raw);

        self::assertCount(3, $treeNode->getChildren());
        self::assertSame($raw, $treeNode->getChildren()[0]->getNode());
        self::assertSame($this->sectionNode1, $treeNode->getChildren()[1]->getNode());
        self::assertSame($this->sectionNode2, $treeNode->getChildren()[2]->getNode());
        self::assertSame($treeNode, $treeNode->getChildren()[0]->getParent());
        self::assertSame($treeNode, $treeNode->getChildren()[0]->getRoot());
        self::assertSame($raw, $this->documentNode->getChildren()[0]);
        self::assertCount(3, $this->documentNode->getChildren());
    }

    public function testPushCompoundChildPropagatesRoot(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);

        $treeNode->pushChild($this->createSection('pushed'));

        $pushed = $treeNode->getChildren()[0];
        self::assertSame($treeNode, $pushed->getRoot());
        self::assertSame($treeNode, $pushed->getChildren()[0]->getRoot());
        self::assertSame($pushed, $pushed->getChildren()[0]->getParent());
    }

    public function testPushChildToNonCompoundNodeThrows(): void
    {
        $leaf = TreeNode::createFromDocument($this->documentNode)->getChildren()[0]->getChildren()[1];

        $this->expectException(LogicException::class);
        $leaf->pushChild(new RawNode('x'));
    }

    public function testRemoveChildDetachesParentAndReindexes(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $third = new RawNode('third');
        $treeNode->addChild($third);
        $removed = $treeNode->getChildren()[0];

        $treeNode->removeChild($this->sectionNode1);

        self::assertNull($removed->getParent());
        self::assertCount(2, $treeNode->getChildren());
        self::assertSame($this->sectionNode2, $treeNode->getChildren()[0]->getNode());
        self::assertSame($third, $treeNode->getChildren()[1]->getNode());
        self::assertSame(0, $treeNode->findPosition($this->sectionNode2));
        self::assertSame(1, $treeNode->findPosition($third));
    }

    public function testRemoveUnknownChildIsNoOp(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $rootNode = $treeNode->getNode();

        $treeNode->removeChild(new RawNode('unknown'));

        self::assertCount(2, $treeNode->getChildren());
        self::assertSame($rootNode, $treeNode->getNode());
        self::assertCount(2, $this->documentNode->getChildren());
    }

    public function testRemoveChildFromNonCompoundNodeThrows(): void
    {
        $leaf = TreeNode::createFromDocument($this->documentNode)->getChildren()[0]->getChildren()[1];

        $this->expectException(LogicException::class);
        $leaf->removeChild(new RawNode('x'));
    }

    public function testReplaceChild(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $replacement = $this->createSection('replacement');

        $treeNode->replaceChild($this->sectionNode1, $replacement);

        self::assertCount(2, $treeNode->getChildren());
        self::assertSame($replacement, $treeNode->getChildren()[0]->getNode());
        self::assertSame($this->sectionNode2, $treeNode->getChildren()[1]->getNode());
        $rootNode = $treeNode->getNode();
        self::assertInstanceOf(CompoundNode::class, $rootNode);
        self::assertSame($replacement, $rootNode->getChildren()[0]);
        self::assertSame($this->sectionNode2, $rootNode->getChildren()[1]);
    }

    public function testReplaceChildPropagatesToParent(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $section = $treeNode->getChildren()[0];
        $oldRaw = $section->getChildren()[0]->getNode();
        $replacement = new RawNode('replaced');

        $section->replaceChild($oldRaw, $replacement);

        self::assertSame($replacement, $section->getChildren()[0]->getNode());
        $sectionNode = $section->getNode();
        self::assertInstanceOf(CompoundNode::class, $sectionNode);
        self::assertSame($replacement, $sectionNode->getChildren()[0]);
        $rootNode = $treeNode->getNode();
        self::assertInstanceOf(CompoundNode::class, $rootNode);
        self::assertSame($sectionNode, $rootNode->getChildren()[0]);
        self::assertSame($treeNode, $section->getParent());
    }

    public function testReplaceChildWithCompoundNodeKeepsStaleShadowChildren(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $replacement = $this->createSection('replacement');
        $oldChildNodes = $this->sectionNode1->getChildren();

        $treeNode->replaceChild($this->sectionNode1, $replacement);

        $replaced = $treeNode->getChildren()[0];
        self::assertSame($replacement, $replaced->getNode());

        // Characterization of current behaviour: the shadow children are not rebuilt from the replacement.
        self::assertCount(count($oldChildNodes), $replaced->getChildren());
        foreach ($replaced->getChildren() as $key => $shadowChild) {
            self::assertSame($oldChildNodes[$key], $shadowChild->getNode());
            self::assertNotSame($replacement->getChildren()[$key], $shadowChild->getNode());
            self::assertSame($replaced, $shadowChild->getParent());
            self::assertSame($treeNode, $shadowChild->getRoot());
        }
    }

    public function testReplaceUnknownChildIsNoOp(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $rootNode = $treeNode->getNode();

        $treeNode->replaceChild(new RawNode('unknown'), new RawNode('other'));

        self::assertSame($rootNode, $treeNode->getNode());
        self::assertSame($this->sectionNode1, $treeNode->getChildren()[0]->getNode());
        self::assertSame($this->sectionNode2, $treeNode->getChildren()[1]->getNode());
    }

    public function testReplaceChildOnNonCompoundNodeThrows(): void
    {
        $leaf = TreeNode::createFromDocument($this->documentNode)->getChildren()[0]->getChildren()[1];

        $this->expectException(LogicException::class);
        $leaf->replaceChild(new RawNode('a'), new RawNode('b'));
    }

    public function testFindPosition(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);

        self::assertSame(0, $treeNode->findPosition($this->sectionNode1));
        self::assertSame(1, $treeNode->findPosition($this->sectionNode2));
    }

    public function testFindPositionReturnsNullForUnknownNode(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);

        self::assertNull($treeNode->findPosition(new RawNode('unknown')));
        self::assertNull($treeNode->findPosition($this->sectionNode1->getChildren()[0]));
    }

    public function testIsLastChildOfParent(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);

        self::assertFalse($treeNode->isLastChildOfParent());
        self::assertFalse($treeNode->getChildren()[0]->isLastChildOfParent());
        self::assertTrue($treeNode->getChildren()[1]->isLastChildOfParent());
    }

    public function testIsLastChildOfParentAfterAddAndPush(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);

        $treeNode->pushChild(new RawNode('pushed'));
        self::assertFalse($treeNode->getChildren()[0]->isLastChildOfParent());
        self::assertTrue($treeNode->getChildren()[2]->isLastChildOfParent());

        $treeNode->addChild(new RawNode('added'));
        self::assertFalse($treeNode->getChildren()[2]->isLastChildOfParent());
        self::assertTrue($treeNode->getChildren()[3]->isLastChildOfParent());
    }

    public function testReleaseClearsReferences(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $child = $treeNode->getChildren()[0];

        $treeNode->release();

        self::assertSame([], $treeNode->getChildren());
        self::assertNull($child->getParent());
        $this->expectException(Error::class);
        $this->expectExceptionMessage('must not be accessed before initialization');
        self::assertNull($child->getRoot());
    }

    public function testAllNodesShareTheOriginalRootAfterCreation(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);

        $this->assertWholeTreeHasRoot($treeNode, $treeNode);
    }

    public function testAllNodesShareTheOriginalRootAfterAddChild(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);

        $treeNode->addChild($this->createSection('added'));
        $treeNode->getChildren()[0]->addChild($this->createSection('nested'));

        $this->assertWholeTreeHasRoot($treeNode, $treeNode);
    }

    public function testAllNodesShareTheOriginalRootAfterPushChild(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);

        $treeNode->pushChild($this->createSection('pushed'));
        $treeNode->getChildren()[1]->pushChild($this->createSection('nested'));

        $this->assertWholeTreeHasRoot($treeNode, $treeNode);
    }

    public function testAllNodesShareTheOriginalRootAfterRemoveChild(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);

        $treeNode->getChildren()[0]->removeChild($this->sectionNode1->getChildren()[1]);
        $treeNode->removeChild($this->sectionNode2);

        $this->assertWholeTreeHasRoot($treeNode, $treeNode);
    }

    public function testAllNodesShareTheOriginalRootAfterReplaceChild(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $section = $treeNode->getChildren()[0];

        $section->replaceChild($this->sectionNode1->getChildren()[1], new RawNode('replaced'));
        $treeNode->replaceChild($this->sectionNode2, $this->createSection('replacement'));

        $this->assertWholeTreeHasRoot($treeNode, $treeNode);
    }

    /**
     * @param TreeNode<DocumentNode> $root
     * @param TreeNode<Node> $node
     */
    private function assertWholeTreeHasRoot(TreeNode $root, TreeNode $node): void
    {
        self::assertSame($root, $node->getRoot());

        foreach ($node->getChildren() as $child) {
            self::assertSame($node, $child->getParent());
            $this->assertWholeTreeHasRoot($root, $child);
        }
    }

    private function createSection(string $title): SectionNode
    {
        $section = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode($title), 1, $title));
        $section->addChildNode(new RawNode('raw'));

        return $section;
    }

    public function testReleaseFreesTheTreeWithoutTheCycleCollector(): void
    {
        $treeNode = TreeNode::createFromDocument($this->documentNode);
        $leaf = WeakReference::create($treeNode->getChildren()[0]->getChildren()[0]);

        gc_disable();
        try {
            $treeNode->release();
            unset($treeNode);

            self::assertNull($leaf->get());
        } finally {
            gc_enable();
        }
    }

    public function testCreateFromDocumentWrapsAttachedNodes(): void
    {
        $term = InlineCompoundNode::getPlainTextInlineNode('term');
        $item = new DefinitionListItemNode($term, []);
        $document = new DocumentNode('test', '/test');
        $document->addChildNode(new DefinitionListNode($item));

        $itemTree = TreeNode::createFromDocument($document)->getChildren()[0]->getChildren()[0];

        self::assertSame($item, $itemTree->getNode());
        self::assertSame([], $itemTree->getChildren());
        self::assertSame(['term'], array_keys($itemTree->getAttachedNodes()));
        self::assertSame($term, $itemTree->getAttachedNodes()['term']->getNode());
        self::assertSame($itemTree, $itemTree->getAttachedNodes()['term']->getParent());
    }

    public function testReplaceChildInAnAttachedNodeChangesTheAttachedNodeItself(): void
    {
        $term = InlineCompoundNode::getPlainTextInlineNode('term');
        $item = new DefinitionListItemNode($term, []);
        $list = new DefinitionListNode($item);
        $document = new DocumentNode('test', '/test');
        $document->addChildNode($list);
        $treeNode = TreeNode::createFromDocument($document);
        $termTree = $treeNode->getChildren()[0]->getChildren()[0]->getAttachedNodes()['term'];
        $replacement = new PlainTextInlineNode('replaced');

        $termTree->replaceChild($termTree->getChildren()[0]->getNode(), $replacement);

        self::assertSame([$replacement], $term->getChildren());
        self::assertSame($term, $termTree->getNode());
        self::assertSame($replacement, $termTree->getChildren()[0]->getNode());
        self::assertSame($document, $treeNode->getNode());
        self::assertSame($list, $document->getChildren()[0]);
        self::assertSame($item, $list->getChildren()[0]);
    }

    public function testRemoveChildFromAnAttachedNodeChangesTheAttachedNodeItself(): void
    {
        $term = new InlineCompoundNode([new PlainTextInlineNode('first'), new PlainTextInlineNode('second')]);
        $document = new DocumentNode('test', '/test');
        $document->addChildNode(new DefinitionListNode(new DefinitionListItemNode($term, [])));
        $termTree = TreeNode::createFromDocument($document)->getChildren()[0]->getChildren()[0]->getAttachedNodes()['term'];
        $second = $term->getChildren()[1];

        $termTree->removeChild($term->getChildren()[0]);

        self::assertSame([$second], $term->getChildren());
        self::assertSame($term, $termTree->getNode());
        self::assertCount(1, $termTree->getChildren());
    }

    public function testReplaceAttachedNodeThrows(): void
    {
        $term = InlineCompoundNode::getPlainTextInlineNode('term');
        $document = new DocumentNode('test', '/test');
        $document->addChildNode(new DefinitionListNode(new DefinitionListItemNode($term, [])));
        $itemTree = TreeNode::createFromDocument($document)->getChildren()[0]->getChildren()[0];

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot replace the attached node "term"');

        $itemTree->replaceChild($term, InlineCompoundNode::getPlainTextInlineNode('replaced'));
    }

    public function testRemoveAttachedNodeThrows(): void
    {
        $term = InlineCompoundNode::getPlainTextInlineNode('term');
        $document = new DocumentNode('test', '/test');
        $document->addChildNode(new DefinitionListNode(new DefinitionListItemNode($term, [])));
        $itemTree = TreeNode::createFromDocument($document)->getChildren()[0]->getChildren()[0];

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot remove the attached node "term"');

        $itemTree->removeChild($term);
    }

    public function testReleaseClearsAttachedNodes(): void
    {
        $document = new DocumentNode('test', '/test');
        $document->addChildNode(new DefinitionListNode(new DefinitionListItemNode(InlineCompoundNode::getPlainTextInlineNode('term'), [])));
        $itemTree = TreeNode::createFromDocument($document)->getChildren()[0]->getChildren()[0];
        $termTree = $itemTree->getAttachedNodes()['term'];

        $itemTree->release();

        self::assertSame([], $itemTree->getAttachedNodes());
        self::assertNull($termTree->getParent());
    }
}
