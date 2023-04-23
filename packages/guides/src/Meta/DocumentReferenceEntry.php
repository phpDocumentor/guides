<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Meta;

class DocumentReferenceEntry implements ChildEntry
{
    public function __construct(private readonly string $file)
    {
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function addChild(ChildEntry $child): void
    {
        //This does not have entries
    }

    /** {@inheritDoc} */
    public function getChildren(): array
    {
        return [];
    }
}
