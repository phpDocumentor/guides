<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\DocumentTree;

use phpDocumentor\Guides\Nodes\AbstractNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\TitleNode;

/** @extends AbstractNode<DocumentNode> */
class DocumentEntryNode extends AbstractNode implements Entry
{
    /** @var Entry[] */
    private array $entries = [];

    public function __construct(private readonly string $file, private readonly TitleNode $titleNode)
    {
    }

    public function getTitle(): TitleNode
    {
        return $this->titleNode;
    }

    public function addChild(Entry $child): void
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
