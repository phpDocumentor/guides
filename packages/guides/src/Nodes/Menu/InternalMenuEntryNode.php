<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Menu;

use phpDocumentor\Guides\Nodes\TitleNode;

final class InternalMenuEntryNode extends MenuEntryNode
{
    /** @var MenuEntryNode[] */
    private array $sections = [];

    /** @param MenuEntryNode[] $children */
    public function __construct(
        private readonly string $url,
        TitleNode $title,
        private array $children = [],
        private readonly bool $isDocumentRoot = false,
        int $level = 1,
        private readonly string $anchor = '',
        private readonly bool $isInRootline = false,
        private readonly bool $isCurrent = false,
        private readonly bool $isExternal = false,
    ) {
        parent::__construct($url, $title, $level);
    }

    public function getDocumentLink(): string
    {
        return '/' . $this->url;
    }

    public function getAnchor(): string
    {
        return $this->anchor;
    }

    /** @return MenuEntryNode[] */
    public function getChildren(): array
    {
        return $this->children;
    }

    /** @return MenuEntryNode[] */
    public function getEntries(): array
    {
        return $this->children;
    }

    public function addMenuEntry(InternalMenuEntryNode $menuEntryNode): void
    {
        $this->children[] = $menuEntryNode;
    }

    public function isDocumentRoot(): bool
    {
        return $this->isDocumentRoot;
    }

    /** @return MenuEntryNode[] */
    public function getSections(): array
    {
        return $this->sections;
    }

    public function addSection(InternalMenuEntryNode $section): void
    {
        $this->sections[] = $section;
    }
    
    public function isInRootline(): bool
    {
        return $this->isInRootline;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function isExternal(): bool
    {
        return $this->isExternal;
    }

    public function __toString(): string
    {
        return $this->url . '#' . $this->anchor;
    }
}
