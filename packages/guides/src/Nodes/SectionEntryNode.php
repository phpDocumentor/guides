<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

use phpDocumentor\Guides\Meta\ChildEntry;
use phpDocumentor\Guides\Meta\InternalTarget;

/** @extends AbstractNode<string> */
class SectionEntryNode extends AbstractNode
{
    /** @var SectionEntryNode[] */
    private array $children = [];

    public function __construct(
        private readonly string|null $title,
        private readonly InternalTarget $internalTarget
    ) {
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getInternalTarget(): InternalTarget
    {
        return $this->internalTarget;
    }

    public function addChild(SectionEntryNode $child): void
    {
        $this->children[] = $child;
    }

    /** {@inheritDoc} */
    public function getChildren(): array
    {
        return $this->children;
    }
}
