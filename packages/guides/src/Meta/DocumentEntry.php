<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Meta;

class DocumentEntry implements Entry
{
    private string $file;
    private array $entries = [];

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function getTitle()
    {
        return $this->entries[0]->getTitle();
    }

    public function addChild(ChildEntry $entry): void
    {
        $this->entries[] = $entry;
    }

    public function getChildren(): array
    {
        return $this->entries;
    }

    public function getFile(): string
    {
        return $this->file;
    }
}
