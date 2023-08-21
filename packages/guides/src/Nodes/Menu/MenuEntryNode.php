<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Menu;

use phpDocumentor\Guides\Nodes\AbstractNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use Stringable;

/** @extends AbstractNode<TitleNode> */
final class MenuEntryNode extends AbstractNode implements Stringable
{
    /** @var MenuEntryNode[] */
    private array $sections = [];

    /** @param MenuEntryNode[] $children */
    public function __construct(
        private readonly string $url,
        TitleNode $title,
        private array $children = [],
        private readonly bool $isDocumentRoot = false,
        private readonly int $level = 1,
        private readonly string $anchor = '',
        private readonly bool $isInRootline = false,
        private readonly bool $isCurrent = false,
    ) {
        $this->value = $title;
    }

    public function getUrl(): string
    {
        return $this->url;
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

    public function addMenuEntry(MenuEntryNode $menuEntryNode): void
    {
        $this->children[] = $menuEntryNode;
    }

    public function isDocumentRoot(): bool
    {
        return $this->isDocumentRoot;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    /** @return MenuEntryNode[] */
    public function getSections(): array
    {
        return $this->sections;
    }

    public function addSection(MenuEntryNode $section): void
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

    public function __toString(): string
    {
        return $this->url . '#' . $this->anchor;
    }
}
