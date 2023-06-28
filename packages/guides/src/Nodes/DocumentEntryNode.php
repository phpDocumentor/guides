<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

use phpDocumentor\Guides\Meta\ChildEntry;

/** @extends AbstractNode<string> */
class DocumentEntryNode extends AbstractNode
{
    /** @var ChildEntry[] */
    private array $entries = [];

    public function __construct(
        private readonly string $file,
        private readonly string|null $title,
        private readonly DocumentNode|null $documentNode = null,
    ) {
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function addChild(ChildEntry $child): void
    {
        $this->entries[] = $child;
    }

    /** @return ChildEntry[] */
    public function getChildren(): array
    {
        return $this->entries;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getDocumentNode(): DocumentNode|null
    {
        return $this->documentNode;
    }
}
