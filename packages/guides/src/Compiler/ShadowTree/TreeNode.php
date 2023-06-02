<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\ShadowTree;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;

class TreeNode
{
    private self|null $parent = null;

    /** @param TreeNode[] $children */
    private function __construct(
        private DocumentNode $root,
        private Node $node,
        private array $children,
    ) {
        foreach ($children as $child) {
            $child->parent = $this;
        }
    }

    public static function createFromDocument(DocumentNode $document): self
    {
        return new self($document, $document, self::createFromCompoundNode($document, $document));
    }

    private static function createFromCompoundNode(CompoundNode $document, DocumentNode $root): array
    {
        $children = [];
        foreach ($document->getChildren() as $child) {
            if ($child instanceof CompoundNode) {
                $children[] = new self($root, $child, self::createFromCompoundNode($child, $root));
                continue;
            }

            $children[] = new self($root, $child, []);
        }

        return $children;
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

    public function getParent(): TreeNode|null
    {
        return $this->parent;
    }
}
