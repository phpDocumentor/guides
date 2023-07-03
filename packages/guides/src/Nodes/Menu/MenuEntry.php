<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Menu;

use phpDocumentor\Guides\Nodes\AbstractNode;
use phpDocumentor\Guides\Nodes\TitleNode;

/** @extends AbstractNode<TitleNode> */
final class MenuEntry extends AbstractNode
{
    /** @param MenuEntry[] $children */
    public function __construct(
        private readonly string $url,
        TitleNode $title,
        private readonly array $children = [],
        private readonly bool $isDocumentRoot = false,
        private readonly int $level = 1,
    ) {
        $this->value = $title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /** @return MenuEntry[] */
    public function getChildren(): array
    {
        return $this->children;
    }

    /** @return MenuEntry[] */
    public function getEntries(): array
    {
        return $this->children;
    }

    public function isDocumentRoot(): bool
    {
        return $this->isDocumentRoot;
    }

    public function getLevel(): int
    {
        return $this->level;
    }
}
