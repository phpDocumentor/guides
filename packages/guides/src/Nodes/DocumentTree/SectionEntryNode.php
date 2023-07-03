<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\DocumentTree;

use phpDocumentor\Guides\Nodes\TitleNode;

class SectionEntryNode
{
    /** @var SectionEntryNode[] */
    private array $children = [];

    public function __construct(private readonly TitleNode $title)
    {
    }

    public function getId(): string
    {
        return $this->title->getId();
    }

    public function getTitle(): TitleNode
    {
        return $this->title;
    }

    public function addChild(SectionEntryNode $child): void
    {
        $this->children[] = $child;
    }

    /** @return SectionEntryNode[] */
    public function getChildren(): array
    {
        return $this->children;
    }
}
