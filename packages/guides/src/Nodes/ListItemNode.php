<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

/**
 * Represents a single item of a bullet or enumerated list.
 *
 * @extends CompoundNode<Node>
 */
final class ListItemNode extends CompoundNode
{
    /** @param Node[] $contents */
    public function __construct(
        private readonly string $prefix,
        private readonly bool $ordered,
        array $contents,
    ) {
        parent::__construct($contents);
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function isOrdered(): bool
    {
        return $this->ordered;
    }
}
