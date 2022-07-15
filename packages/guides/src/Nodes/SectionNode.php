<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

final class SectionNode extends Node
{
    private TitleNode $title;
    /** @var Node[] */
    private array $nodes = [];

    public function __construct(TitleNode $title)
    {
        $this->title = $title;
    }

    public function getTitle(): TitleNode
    {
        return $this->title;
    }

    public function addNode(Node $node): void
    {
        $this->nodes[] = $node;
    }

    /** @return Node[] */
    public function getNodes(): array
    {
        return $this->nodes;
    }
}
