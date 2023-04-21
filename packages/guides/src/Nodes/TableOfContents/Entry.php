<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\TableOfContents;

use phpDocumentor\Guides\Nodes\AbstractNode;
use phpDocumentor\Guides\Nodes\TitleNode;

/** @extends AbstractNode<TitleNode> */
final class Entry extends AbstractNode
{
    /** @param Entry[] $children */
    public function __construct(private string $url, TitleNode $title, private array $children = [], private bool $isDocumentRoot = false)
    {
        $this->value = $title;
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

    public function isDocumentRoot(): bool
    {
        return $this->isDocumentRoot;
    }
}
