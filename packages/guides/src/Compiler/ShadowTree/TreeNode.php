<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\ShadowTree;

use LogicException;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;

use function array_values;

class TreeNode
{
    /** @param TreeNode[] $children */
    private function __construct(
        private DocumentNode $root,
        private Node $node,
        private array $children,
        private self|null $parent = null,
    ) {
        foreach ($children as $child) {
            $child->parent = $this;
        }
    }

    public static function createFromDocument(DocumentNode $document): self
    {
        return new self($document, $document, self::createFromCompoundNode($document, $document));
    }

    /**
     * @param CompoundNode<Node> $node
     *
     * @return TreeNode[]
     */
    private static function createFromCompoundNode(CompoundNode $node, DocumentNode $root): array
    {
        $children = [];
        foreach ($node->getChildren() as $child) {
            $children[] = self::createFromNode($child, $root);
        }

        return $children;
    }

    private static function createFromNode(Node $node, DocumentNode $root, self|null $parent = null): self
    {
        if ($node instanceof CompoundNode) {
            return new self($root, $node, self::createFromCompoundNode($node, $root), $parent);
        }

        return new self($root, $node, [], $parent);
    }

    public function getRoot(): DocumentNode
    {
        return $this->root;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    /** @return TreeNode[] */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChild(Node $child): void
    {
        if ($this->node instanceof CompoundNode === false) {
            throw new LogicException('Cannot add a child to a non-compound node');
        }

        $this->children[] = self::createFromNode($child, $this->root, $this);
        $this->node->addChildNode($child);
    }

    public function getParent(): TreeNode|null
    {
        return $this->parent;
    }

    public function removeChild(Node $node): void
    {
        if ($this->node instanceof CompoundNode === false) {
            throw new LogicException('Cannot remove a child from a non-compound node');
        }

        foreach ($this->children as $key => $child) {
            if ($child->getNode() === $node) {
                unset($this->children[$key]);
                $child->parent = null;
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
        if ($this->node instanceof CompoundNode === false) {
            throw new LogicException('Cannot remove a child from a non-compound node');
        }

        foreach ($this->children as $key => $child) {
            if ($child->getNode() === $oldChildNode) {
                $child->node = $newChildNode;
                $newNode = $this->node->replaceNode($key, $newChildNode);
                $this->parent?->replaceChild($this->node, $newNode);
                $this->node = $newNode;
                break;
            }
        }
    }
}
