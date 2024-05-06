<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\Nodes;

/** @extends CompoundNode<ListItemNode> */
final class ListNode extends CompoundNode
{
    /** @param ListItemNode[] $items */
    public function __construct(
        array $items,
        private readonly bool $ordered = false,
        private string|null $start = null,
        private string|null $orderingType = null,
    ) {
        parent::__construct($items);
    }

    public function getStart(): string|null
    {
        return $this->start;
    }

    public function setStart(string|null $start): ListNode
    {
        $this->start = $start;

        return $this;
    }

    public function getOrderingType(): string|null
    {
        return $this->orderingType;
    }

    public function setOrderingType(string|null $orderingType): ListNode
    {
        $this->orderingType = $orderingType;

        return $this;
    }

    public function isOrdered(): bool
    {
        return $this->ordered;
    }
}
