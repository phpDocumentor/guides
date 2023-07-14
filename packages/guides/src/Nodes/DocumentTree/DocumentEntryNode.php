<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\DocumentTree;

use phpDocumentor\Guides\Nodes\AbstractNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\TitleNode;

/** @extends AbstractNode<DocumentNode> */
final class DocumentEntryNode extends AbstractNode
{
    /** @var DocumentEntryNode[] */
    private array $entries = [];
    /** @var SectionEntryNode[]  */
    private array $sections = [];
    private DocumentEntryNode|null $parent = null;

    public function __construct(
        private readonly string $file,
        private readonly TitleNode $titleNode,
        private readonly bool $isRoot = false,
    ) {
    }

    public function getTitle(): TitleNode
    {
        return $this->titleNode;
    }

    public function addChild(DocumentEntryNode $child): void
    {
        $this->entries[] = $child;
    }

    /** @return DocumentEntryNode[] */
    public function getChildren(): array
    {
        return $this->entries;
    }

    public function getParent(): DocumentEntryNode|null
    {
        return $this->parent;
    }

    public function setParent(DocumentEntryNode|null $parent): void
    {
        $this->parent = $parent;
    }

    /** @return SectionEntryNode[] */
    public function getSections(): array
    {
        return $this->sections;
    }

    public function addSection(SectionEntryNode $section): void
    {
        $this->sections[] = $section;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function isRoot(): bool
    {
        return $this->isRoot;
    }
}
