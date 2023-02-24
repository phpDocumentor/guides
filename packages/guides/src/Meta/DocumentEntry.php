<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Meta;

use phpDocumentor\Guides\Nodes\TitleNode;

class DocumentEntry implements Entry
{
    private string $file;

    /** @var ChildEntry[] */
    private array $entries = [];
    private TitleNode $titleNode;

    public function __construct(string $file, TitleNode $titleNode)
    {
        $this->file = $file;
        $this->titleNode = $titleNode;
    }

    public function getTitle(): TitleNode
    {
        return $this->titleNode;
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
