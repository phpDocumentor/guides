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

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\CompoundNode;

/**
 * @extends CompoundNode<Node>
 */
final class TopicNode extends CompoundNode
{
    private string $name;

    public function __construct(string $name, ?Node $value = null)
    {
        parent::__construct(array_filter([$value]));
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
