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

use function implode;
use function strlen;
use function substr;
use function trim;

/**
 * @extends AbstractNode<Node[]>
 */
abstract class CompoundNode extends AbstractNode
{
    /**
     * @param Node[] $value
     */
    public function __construct(array $value = [])
    {
        $this->value = $value;
    }

    /** @return Node[] */
    public function getChildren(): array
    {
        return $this->value;
    }

    public function addChildNode(Node $node): void
    {
        $this->value[] = $node;
    }

    public function removeNode(int $key): self
    {
        $result = clone $this;
        unset($result->value[$key]);

        return $result;
    }

    public function replaceNode(int $key, Node $node): self
    {
        $result = clone $this;
        $result->value[$key] = $node;
        return $result;
    }
}
