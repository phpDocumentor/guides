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

namespace phpDocumentor\Guides\RestructuredText\Nodes;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;

/** @extends CompoundNode<Node> */
final class SidebarNode extends CompoundNode
{
    private string $title;

    /** {@inheritDoc} */
    public function __construct(string $title, array $value)
    {
        parent::__construct($value);

        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
