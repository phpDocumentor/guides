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

use LogicException;
use phpDocumentor\Guides\Compiler\CoreProvider;
use phpDocumentor\Guides\Compiler\SubNodeAccessorRegistry;
use phpDocumentor\Guides\Nodes\AdmonitionNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionListItemNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use PHPUnit\Framework\TestCase;

use function array_map;

final class TreeNodeFieldsTest extends TestCase
{
    private DefinitionListItemNode $item;
    private DocumentNode $document;

    protected function setUp(): void
    {
        $this->item = new DefinitionListItemNode(
            InlineCompoundNode::getPlainTextInlineNode('term'),
            [InlineCompoundNode::getPlainTextInlineNode('a'), InlineCompoundNode::getPlainTextInlineNode('b')],
        );
        $this->document = new DocumentNode('1', 'index');
        $this->document->addChildNode($this->item);
    }

    /** @return TreeNode<DocumentNode> */
    private function tree(): TreeNode
    {
        return TreeNode::createFromDocument($this->document, new SubNodeAccessorRegistry([new CoreProvider()]));
    }

    /**
     * @param TreeNode<DocumentNode> $tree
     *
     * @return TreeNode<Node>
     */
    private function itemTree(TreeNode $tree): TreeNode
    {
        return $tree->getChildren()[0];
    }

    public function testRegisteredFieldsBecomeAttachedNodes(): void
    {
        $item = $this->itemTree($this->tree());

        self::assertCount(3, $item->getTraversalChildren());
    }

    public function testTraversalChildrenListRegisteredFieldsBeforeOrdinaryChildren(): void
    {
        $definition = new DefinitionNode([]);
        $item = new DefinitionListItemNode(InlineCompoundNode::getPlainTextInlineNode('term'), [], [$definition]);
        $document = new DocumentNode('1', 'index');
        $document->addChildNode($item);
        $tree = TreeNode::createFromDocument($document, new SubNodeAccessorRegistry([new CoreProvider()]));

        $nodes = array_map(static fn (TreeNode $child): Node => $child->getNode(), $tree->getChildren()[0]->getTraversalChildren());

        self::assertSame([$item->getTerm(), $definition], $nodes);
    }

    public function testReplacingScalarFieldWritesBackThroughImmutableOwners(): void
    {
        $tree = $this->tree();
        $item = $this->itemTree($tree);
        $new = InlineCompoundNode::getPlainTextInlineNode('new');

        $item->replaceChild($item->getTraversalChildren()[0]->getNode(), $new);

        $updated = $this->itemTree($tree)->getNode();
        self::assertInstanceOf(DefinitionListItemNode::class, $updated);
        self::assertSame($new, $updated->getTerm());
        self::assertNotSame($this->item, $updated);
        self::assertSame($this->item->getClassifiers(), $updated->getClassifiers());
        self::assertSame($updated, $tree->getNode()->getChildren()[0]);
    }

    public function testReplacingAndRemovingListItems(): void
    {
        $tree = $this->tree();
        $item = $this->itemTree($tree);
        $classifierA = $item->getTraversalChildren()[1]->getNode();
        $classifierB = $item->getTraversalChildren()[2]->getNode();
        $new = InlineCompoundNode::getPlainTextInlineNode('new');

        $item->replaceChild($classifierB, $new);
        $item->removeChild($classifierA);

        $updated = $this->itemTree($tree)->getNode();
        self::assertInstanceOf(DefinitionListItemNode::class, $updated);
        self::assertSame([1 => $new], $updated->getClassifiers());
        self::assertCount(2, $item->getTraversalChildren());
    }

    public function testRemovingNonNullableFieldFails(): void
    {
        $item = $this->itemTree($this->tree());

        $this->expectException(LogicException::class);
        $item->removeChild($item->getTraversalChildren()[0]->getNode());
    }

    public function testRemovingNullableFieldClearsIt(): void
    {
        $title = InlineCompoundNode::getPlainTextInlineNode('title');
        $document = new DocumentNode('1', 'index');
        $document->addChildNode(new AdmonitionNode('note', $title, 'note', []));
        $tree = TreeNode::createFromDocument($document, new SubNodeAccessorRegistry([new CoreProvider()]));
        $admonition = $tree->getChildren()[0];

        $admonition->removeChild($title);

        $updated = $tree->getNode()->getChildren()[0];
        self::assertInstanceOf(AdmonitionNode::class, $updated);
        self::assertNull($updated->getTitle());
    }
}
