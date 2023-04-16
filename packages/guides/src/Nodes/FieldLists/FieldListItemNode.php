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

namespace phpDocumentor\Guides\Nodes\FieldLists;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;

/**
 * @extends CompoundNode<Node>
 */
final class FieldListItemNode extends CompoundNode
{
    private string $term;

    /**
     * @param Node[] $children
     */
    public function __construct(string $term, array $children = [])
    {
        $this->term = $term;
        parent::__construct($children);
    }

    public function getTerm(): string
    {
        return $this->term;
    }
}
