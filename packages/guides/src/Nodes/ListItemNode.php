<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

use function array_reduce;
use function trim;

/**
 * Represents a single item of a bullet or enumerated list.
 */
final class ListItemNode extends Node
{
    /** @var string the list marker used for this item */
    private string $prefix;

    /** @var bool whether the list marker represents an enumerated list */
    private bool $ordered;

    /** @var Node[] */
    private array $nodes;

    /**
     * @param Node[] $contents
     */
    public function __construct(string $prefix, bool $ordered, array $contents)
    {
        $this->prefix   = $prefix;
        $this->ordered  = $ordered;
        $this->nodes = $contents;

        parent::__construct(null);
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function isOrdered(): bool
    {
        return $this->ordered;
    }

    /**
     * @return Node[]
     */
    public function getChildren(): array
    {
        return $this->nodes;
    }

    public function addChildNode(Node $node): void
    {
        $this->nodes[] = $node;
    }
}
