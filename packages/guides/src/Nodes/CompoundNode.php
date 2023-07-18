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

use function array_unshift;
use function array_values;

/**
 * @template TValue as Node
 * @extends AbstractNode<TValue[]>
 */
abstract class CompoundNode extends AbstractNode
{
    /** @param list<TValue> $value */
    public function __construct(array $value = [])
    {
        $this->value = $value;
    }

    /** @return TValue[] */
    public function getChildren(): array
    {
        return $this->value;
    }

    /** @param TValue $node */
    public function addChildNode(Node $node): void
    {
        $this->value[] = $node;
    }

    /** @param TValue $node */
    public function pushChildNode(Node $node): void
    {
        array_unshift($this->value, $node);
    }

    /** @return $this<TValue> */
    public function removeNode(int $key): self
    {
        $result = clone $this;
        unset($result->value[$key]);
        $result->value = array_values($result->value);

        return $result;
    }

    /**
     * @param TValue $node
     *
     * @return $this<TValue>
     */
    public function replaceNode(int $key, Node $node): self
    {
        $result = clone $this;
        $result->value[$key] = $node;

        return $result;
    }
}
