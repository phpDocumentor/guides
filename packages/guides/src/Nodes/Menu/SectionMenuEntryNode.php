<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Menu;

use phpDocumentor\Guides\Nodes\TitleNode;

final class SectionMenuEntryNode extends MenuEntryNode
{
    /** @var SectionMenuEntryNode[] */
    private array $sections = [];

    public function __construct(
        private readonly string $url,
        TitleNode|null $title = null,
        int $level = 1,
        private readonly string $anchor = '',
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

    /** @return SectionMenuEntryNode[] */
    public function getSections(): array
    {
        return $this->sections;
    }

    public function addSection(SectionMenuEntryNode $section): void
    {
        $this->sections[] = $section;
    }

    public function __toString(): string
    {
        return $this->url . '#' . $this->anchor;
    }
}
