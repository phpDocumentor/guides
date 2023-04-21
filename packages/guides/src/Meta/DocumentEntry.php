<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Meta;

use phpDocumentor\Guides\Nodes\TitleNode;

class DocumentEntry implements Entry
{
    /** @var ChildEntry[] */
    private array $entries = [];

    public function __construct(private string $file, private TitleNode $titleNode)
    {
    }

    public function getTitle(): TitleNode
    {
        return $this->titleNode;
    }

    public function addChild(ChildEntry $child): void
    {
        $this->entries[] = $child;
    }

    /** {@inheritDoc} */
    public function getChildren(): array
    {
        return $this->entries;
    }

    public function getFile(): string
    {
        return $this->file;
    }
}
