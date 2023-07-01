<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Meta;

use phpDocumentor\Guides\Nodes\DocumentTree\Entry;

class DocumentReferenceEntry implements Entry
{
    public function __construct(private readonly string $file)
    {
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function addChild(Entry $child): void
    {
        //This does not have entries
    }

    /** {@inheritDoc} */
    public function getChildren(): array
    {
        return [];
    }
}
