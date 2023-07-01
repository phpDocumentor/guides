<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Menu;

use phpDocumentor\Guides\Nodes\AbstractNode;
use phpDocumentor\Guides\Nodes\TitleNode;

/** @extends AbstractNode<TitleNode> */
final class Entry extends AbstractNode
{
    /** @param Entry[] $children */
    public function __construct(private readonly string $url, TitleNode $title, private readonly array $children = [], private readonly bool $isDocumentRoot = false)
    {
        $this->value = $title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /** @return Entry[] */
    public function getChildren(): array
    {
        return $this->children;
    }

    /** @return Entry[] */
    public function getEntries(): array
    {
        return $this->children;
    }

    public function isDocumentRoot(): bool
    {
        return $this->isDocumentRoot;
    }
}
