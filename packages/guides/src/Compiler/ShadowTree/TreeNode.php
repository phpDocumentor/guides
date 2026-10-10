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
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\HasAttachedNodes;
use phpDocumentor\Guides\Nodes\Node;

use function array_unshift;
use function array_values;
use function count;
use function sprintf;

/** @template-covariant TNode of Node */
final class TreeNode
{
    /** @var TreeNode<DocumentNode> */
    private TreeNode $root;

    /** @var self<Node>[] */
    private array $children = [];

    /** @var array<string, self<Node>> */
    private array $attachedNodes = [];

    /**
     * An attached node is held in a property of its parent node, which cannot take a copy of
     * it, so changes to its children are made in the attached node itself.
     */
    private bool $isAttached = false;

    private function __construct(
        /** @var TNode */
        private Node $node,
        /** @var self<Node>|self<DocumentNode>|null */
        private self|null $parent = null,
    ) {
    }

    /** @return TreeNode<DocumentNode> */
    public static function createFromDocument(DocumentNode $document): self
    {
        $node = new self($document);
        $node->root = $node;
        $node->setChildren(self::createFromCompoundNode($document, $node));

        return $node;
    }

    /**
     * @param CompoundNode<Node> $node
     * @param self<Node>|self<DocumentNode> $parent
     *
     * @return TreeNode<Node>[]
     */
    private static function createFromCompoundNode(CompoundNode $node, self $parent): array
    {
        $children = [];
        foreach ($node->getChildren() as $child) {
            $children[] = self::createFromNode($child, $parent);
        }

        return $children;
    }

    /**
     * @param TValue $node
     * @param self<Node>|self<DocumentNode> $parent
     *
     * @return TreeNode<TValue>
     *
     * @template TValue of Node
     */
    private static function createFromNode(Node $node, self $parent): self
    {
        $treeNode = new self($node, $parent);
        $treeNode->root = $parent->root;
        if ($node instanceof HasAttachedNodes) {
            foreach ($node->getAttachedNodes() as $name => $attachedNode) {
                $treeNode->attachedNodes[$name] = self::createFromNode($attachedNode, $treeNode);
                $treeNode->attachedNodes[$name]->isAttached = true;
            }
        }

        if ($node instanceof CompoundNode === false) {
            return $treeNode;
        }

        $treeNode->setChildren(self::createFromCompoundNode($node, $treeNode));

        return $treeNode;
    }

    /** @param self<Node>[] $children */
    private function setChildren(array $children): void
    {
        foreach ($children as $child) {
            $child->parent = $this;
        }

        $this->children = $children;
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
     * The nodes the node keeps beside its children, see {@see HasAttachedNodes}.
     *
     * @return array<string, TreeNode<Node>>
     */
    public function getAttachedNodes(): array
    {
        return $this->attachedNodes;
    }

    public function addChild(Node $child): void
    {
        if ($this->node instanceof CompoundNode === false) {
            throw new LogicException('Cannot add a child to a non-compound node');
        }

        $this->children[] = self::createFromNode($child, $this);
        $this->node->addChildNode($child);
    }

    public function pushChild(Node $child): void
    {
        if ($this->node instanceof CompoundNode === false) {
            throw new LogicException('Cannot add a child to a non-compound node');
        }

        array_unshift($this->children, self::createFromNode($child, $this));
        $this->node->pushChildNode($child);
    }

    /** @return self<Node>|self<DocumentNode>|null */
    public function getParent(): TreeNode|null
    {
        return $this->parent;
    }

    public function removeChild(Node $node): void
    {
        $this->assertNotAttached($node, 'remove');

        if ($this->node instanceof CompoundNode === false) {
            throw new LogicException('Cannot remove a child from a non-compound node');
        }

        foreach ($this->children as $key => $child) {
            if ($child->getNode() === $node) {
                unset($this->children[$key]);
                $child->parent = null;
                if ($this->isAttached) {
                    $children = $this->node->getChildren();
                    unset($children[$key]);
                    $this->node->setValue(array_values($children));
                    break;
                }

                $newNode = $this->node->removeNode($key);
                $this->parent?->replaceChild($this->node, $newNode);
                $this->node = $newNode;
                break;
            }
        }

        $this->children = array_values($this->children);
    }

    public function replaceChild(Node $oldChildNode, Node $newChildNode): void
    {
        $this->assertNotAttached($oldChildNode, 'replace');

        if ($this->node instanceof CompoundNode === false) {
            throw new LogicException('Cannot remove a child from a non-compound node');
        }

        foreach ($this->children as $key => $child) {
            if ($child->getNode() === $oldChildNode) {
                $child->node = $newChildNode;
                if ($this->isAttached) {
                    $children = $this->node->getChildren();
                    $children[$key] = $newChildNode;
                    $this->node->setValue($children);
                    break;
                }

                $newNode = $this->node->replaceNode($key, $newChildNode);
                $this->parent?->replaceChild($this->node, $newNode);
                $this->node = $newNode;
                break;
            }
        }
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

    private function assertNotAttached(Node $node, string $action): void
    {
        foreach ($this->attachedNodes as $name => $attachedNode) {
            if ($attachedNode->getNode() === $node) {
                throw new LogicException(sprintf('Cannot %s the attached node "%s", change its children instead', $action, $name));
            }
        }
    }
}
