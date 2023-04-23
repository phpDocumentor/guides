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
    public function __construct(private readonly array $items, private readonly bool $ordered = false)
    {
        parent::__construct();
    }

    /** @return ListItemNode[] */
    public function getChildren(): array
    {
        return $this->items;
    }

    public function isOrdered(): bool
    {
        return $this->ordered;
    }
}
