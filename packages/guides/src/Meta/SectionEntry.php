<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Meta;

use phpDocumentor\Guides\Nodes\TitleNode;

class SectionEntry implements ChildEntry
{
    /** @var ChildEntry[] */
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

    public function addChild(ChildEntry $child): void
    {
        $this->children[] = $child;
    }

    /** {@inheritDoc} */
    public function getChildren(): array
    {
        return $this->children;
    }
}
