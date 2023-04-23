<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

use function array_merge;

/** @extends CompoundNode<Node> */
final class SectionNode extends CompoundNode
{
    public function __construct(private readonly TitleNode $title)
    {
        parent::__construct();
    }

    public function getTitle(): TitleNode
    {
        return $this->title;
    }

    /** @return TitleNode[] */
    public function getTitles(): array
    {
        $titles = [$this->title];
        foreach ($this->value as $node) {
            if ($node instanceof self === false) {
                continue;
            }

            $titles = array_merge($titles, $node->getTitles());
        }

        return $titles;
    }
}
