<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

final class SectionNode extends CompoundNode
{
    private TitleNode $title;
    /** @var \phpDocumentor\Guides\Nodes\CompoundNode[] */
    private array $nodes = [];

    public function __construct(TitleNode $title)
    {
        parent::__construct();
        $this->title = $title;
    }

    public function getTitle(): TitleNode
    {
        return $this->title;
    }

    /** @return TitleNode[] */
    public function getTitles(): array
    {
        $titles = [$this->getTitle()];
        foreach ($this->nodes as $node) {
            if ($node instanceof self === false) {
                continue;
            }

            $titles = array_merge($titles, $node->getTitles());
        }

        return $titles;
    }
}
