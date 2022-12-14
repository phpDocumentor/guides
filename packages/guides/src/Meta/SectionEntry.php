<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Meta;

use phpDocumentor\Guides\Nodes\TitleNode;

class SectionEntry implements ChildEntry
{
    private TitleNode $title;

    /** @var ChildEntry[] */
    private array $children;

    public function __construct(TitleNode $title)
    {
        $this->title = $title;
    }

    public function addChild(ChildEntry $child): void
    {
        $this->children[] = $child;
    }

    public function getChildren(): array
    {
        return $this->children;
    }
}
