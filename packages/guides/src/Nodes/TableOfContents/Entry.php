<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\TableOfContents;

use phpDocumentor\Guides\Nodes\AbstractNode;
use phpDocumentor\Guides\Nodes\TitleNode;

/** @extends AbstractNode<TitleNode> */
final class Entry extends AbstractNode
{
    private string $url;

    /** @var Entry[] */
    private array $children;

    private bool $isDocumentRoot;

    /** @param Entry[] $children */
    public function __construct(string $url, TitleNode $title, array $children = [], bool $isDocumentRoot = false)
    {
        $this->url = $url;
        $this->value = $title;
        $this->children = $children;
        $this->isDocumentRoot = $isDocumentRoot;
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
