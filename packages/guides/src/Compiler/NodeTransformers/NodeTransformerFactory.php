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

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\Node;

interface NodeTransformerFactory
{
    /** @return iterable<NodeTransformer<Node>> */
    public function getTransformers(): iterable;

    /** @return int[] */
    public function getPriorities(): array;
}
