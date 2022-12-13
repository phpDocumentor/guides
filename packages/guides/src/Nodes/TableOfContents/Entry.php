<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\TableOfContents;

use phpDocumentor\Guides\Nodes\TitleNode;

final class Entry
{
    /** @var string */
    private $url;

    private TitleNode $title;

    /** @var string|null */
    private $parent;

    /** @var Entry[] */
    private $children;

    public function __construct(string $url, TitleNode $title, ?string $parent = null)
    {
        $this->url = $url;
        $this->title = $title;
        $this->parent = $parent;
        $this->children = [];
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTitle(): TitleNode
    {
        return $this->title;
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function addChild(Entry $child): void
    {
        $this->children[] = $child;
    }

    /** @return Entry[] */
    public function getEntries(): array
    {
        return $this->children;
    }
}
