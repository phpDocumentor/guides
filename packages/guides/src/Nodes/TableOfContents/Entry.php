<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\TableOfContents;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;

final class Entry extends Node
{
    private string $url;

    /** @var Entry[] */
    private array $children;

    /** @param Entry[] $children */
    public function __construct(string $url, TitleNode $title, array $children = [])
    {
        $this->url = $url;
        parent::__construct($title);
        $this->children = $children;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /** @return Entry[] */
    public function getEntries(): array
    {
        return $this->children;
    }
}
