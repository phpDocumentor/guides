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
        private readonly string|null $orderNumber = null,
        private readonly string|null $orderType = null,
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

    public function getOrderNumber(): string|null
    {
        return $this->orderNumber;
    }

    public function getOrderType(): string|null
    {
        return $this->orderType;
    }
}
