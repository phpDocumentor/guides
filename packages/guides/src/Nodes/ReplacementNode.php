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

/** @extends CompoundNode<Node> */
final class ReplacementNode extends CompoundNode
{
    /** @param Node[] $children */
    public function __construct(array $children)
    {
        parent::__construct($children);
    }

    /** @return Node[] */
    public function getChildren(): array
    {
        return $this->getValue();
    }
}
