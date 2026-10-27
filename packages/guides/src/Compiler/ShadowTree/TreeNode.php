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
use phpDocumentor\Guides\Compiler\ChildrenAccessor;
use phpDocumentor\Guides\Compiler\SubNodeAccessor;
use phpDocumentor\Guides\Compiler\SubNodeAccessorRegistry;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;

use function array_filter;
use function array_unshift;
use function array_values;
use function count;

/** @template-covariant TNode of Node */
final class TreeNode
{
    /** @var TreeNode<DocumentNode> */
    private TreeNode $root;

    /** @var self<Node>[] */
    private array $children = [];

    /** @var self<Node>[] children stored in registered fields, rather than as ordinary children */
    private array $attachedNodes = [];

    private function __construct(
        /** @var TNode */
        private Node $node,
        /** @var self<Node>|self<DocumentNode>|null */
        private self|null $parent = null,
        private readonly SubNodeAccessorRegistry $attachedNodeRegistry = new SubNodeAccessorRegistry([]),
        /** The field of the parent this node was extracted from, used to write changes back */
        private readonly SubNodeAccessor|null $field = null,
        private int|string|null $key = null,
    ) {
    }

    /** @return TreeNode<DocumentNode> */
    public static function createFromDocument(DocumentNode $document, SubNodeAccessorRegistry|null $attachedNodeRegistry = null): self
    {
        $node = new self($document, null, $attachedNodeRegistry ?? new SubNodeAccessorRegistry([]));
        $node->root = $node;
        $node->createChildren();

        return $node;
    }

    /**
     * @param TValue $node
     * @param self<Node>|self<DocumentNode> $parent
     *
     * @return TreeNode<TValue>
     *
     * @template TValue of Node
     */
    private static function createFromNode(Node $node, self $parent, SubNodeAccessor $field, int|string|null $key = null): self
    {
        $treeNode = new self($node, $parent, $parent->attachedNodeRegistry, $field, $key);
        $treeNode->root = $parent->root;
        $treeNode->createChildren();

        return $treeNode;
    }

    private function createChildren(): void
    {
        $fields = $this->attachedNodeRegistry->getFields($this->node);
        foreach ($fields?->all() ?? [] as $field) {
            foreach ($field->items($this->node) as $key => $item) {
                $this->attachedNodes[] = self::createFromNode($item, $this, $field, $key);
            }
        }

        if ($this->node instanceof CompoundNode === false) {
            return;
        }

        $field = new ChildrenAccessor();
        foreach ($field->items($this->node) as $key => $item) {
            $this->children[] = self::createFromNode($item, $this, $field, $key);
        }
    }

    /** @return self<DocumentNode> */
    public function getRoot(): self
    {
        return $this->root;
    }

    /** @return TNode */
    public function getNode(): Node
    {
        return $this->node;
    }

    /** @return  TreeNode<Node>[] */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Returns the nodes stored in registered fields, followed by the ordinary children.
     *
     * @return self<Node>[]
     */
    public function getTraversalChildren(): array
    {
        return [...$this->attachedNodes, ...$this->children];
    }

    public function addChild(Node $child): void
    {
        if ($this->node instanceof CompoundNode === false) {
            throw new LogicException('Cannot add a child to a non-compound node');
        }

        $this->children[] = self::createFromNode($child, $this, new ChildrenAccessor());
        $this->node->addChildNode($child);
    }

    public function pushChild(Node $child): void
    {
        if ($this->node instanceof CompoundNode === false) {
            throw new LogicException('Cannot add a child to a non-compound node');
        }

        array_unshift($this->children, self::createFromNode($child, $this, new ChildrenAccessor()));
        $this->node->pushChildNode($child);
    }

    /** @return self<Node>|self<DocumentNode>|null */
    public function getParent(): TreeNode|null
    {
        return $this->parent;
    }

    public function removeChild(Node $node): void
    {
        $child = $this->findEdge($node, 'remove');
        if ($child?->field === null) {
            return;
        }

        $newNode = $child->field->removeAt($this->node, $this->keyOf($child));

        $child->parent = null;
        $this->children = array_values(array_filter($this->children, static fn (self $c): bool => $c !== $child));
        $this->attachedNodes = array_values(array_filter($this->attachedNodes, static fn (self $c): bool => $c !== $child));

        $this->changeNode($newNode);
    }

    public function replaceChild(Node $oldChildNode, Node $newChildNode): void
    {
        $child = $this->findEdge($oldChildNode, 'replace');
        if ($child?->field === null) {
            return;
        }

        $newNode = $child->field->replaceAt($this->node, $this->keyOf($child), $newChildNode);
        $child->node = $newChildNode;

        $this->changeNode($newNode);
    }

    public function findPosition(Node $node): int|null
    {
        foreach ($this->children as $key => $child) {
            if ($child->getNode() === $node) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Shadow trees are reference cycles (child to parent and to root), so without this
     * they're only freed by the cycle collector, which also has to walk the whole AST.
     */
    public function release(): void
    {
        foreach ($this->attachedNodes as $attachedNode) {
            $attachedNode->release();
        }

        foreach ($this->children as $child) {
            $child->release();
        }

        $this->attachedNodes = [];
        $this->children = [];
        $this->parent = null;
        unset($this->root);
    }

    public function isLastChildOfParent(): bool
    {
        if ($this->parent === null) {
            return false;
        }

        return $this->parent->findPosition($this->node) === count($this->parent->getChildren()) - 1;
    }

    /** @return self<Node>|null */
    private function findEdge(Node $node, string $action): self|null
    {
        foreach ([...$this->attachedNodes, ...$this->children] as $child) {
            if ($child->getNode() === $node) {
                return $child;
            }
        }

        if ($this->node instanceof CompoundNode === false && $this->attachedNodes === []) {
            throw new LogicException('Cannot ' . $action . ' a child of a non-compound node');
        }

        return null;
    }

    /**
     * Ordinary children shift when siblings are removed or pushed, so their index is derived from the
     * current position. Other fields keep the key they were extracted with.
     *
     * @param self<Node> $child
     */
    private function keyOf(self $child): int|string|null
    {
        if ($child->field instanceof ChildrenAccessor) {
            return $this->findPosition($child->node);
        }

        return $child->key;
    }

    /** Stores the new version of this node, and writes it back to the field of its parent. */
    private function changeNode(Node $newNode): void
    {
        if ($this->parent === null) {
            /** @phpstan-ignore assign.propertyType */
            $this->node = $newNode;

            return;
        }

        $parent = $this->parent;
        $field = $this->field;
        if ($field === null) {
            return;
        }

        $newParent = $field->replaceAt($parent->node, $parent->keyOf($this), $newNode);
        /** @phpstan-ignore assign.propertyType */
        $this->node = $newNode;
        $parent->changeNode($newParent);
    }
}
