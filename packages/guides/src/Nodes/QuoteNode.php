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
final class QuoteNode extends CompoundNode
{
    /**
     * @param list<Node> $value
     * @param string[] $classes
     */
    public function __construct(array $value = [], array $classes = [])
    {
        parent::__construct($value);

        $this->classes = $classes;
    }
}
