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

/**
 * @extends CompoundNode<FieldNode>
 */
final class FieldListItemNode extends CompoundNode
{
    private string $term;

    /**
     * @param FieldNode[] $definitions
     */
    public function __construct(string $term, array $definitions = [])
    {
        $this->term = $term;
        parent::__construct($definitions);
    }

    public function getTerm(): string
    {
        return $this->term;
    }
}
