<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Meta;

class DocumentReferenceEntry implements ChildEntry
{
    private string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function addChild(ChildEntry $child): void
    {
        //This does not have entries
    }

    public function getChildren(): array
    {
        return [];
    }
}
