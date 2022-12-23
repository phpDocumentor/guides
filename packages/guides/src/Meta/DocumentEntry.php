<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Meta;

use phpDocumentor\Guides\Nodes\TitleNode;

class DocumentEntry implements Entry
{
    private string $file;

    /** @var ChildEntry[] */
    private array $entries = [];

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function getTitle(): TitleNode
    {
        return isset($this->entries[0]) ? $this->entries[0]->getTitle() : TitleNode::emptyNode();
    }

    public function addChild(ChildEntry $entry): void
    {
        $this->entries[] = $entry;
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
}
