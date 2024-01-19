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

use function array_map;
use function array_unique;
use function sort;

final class CustomNodeTransformerFactory implements NodeTransformerFactory
{
    /** @param iterable<NodeTransformer<Node>> $transformers */
    public function __construct(private readonly iterable $transformers)
    {
    }

    /** @return iterable<NodeTransformer<Node>> */
    public function getTransformers(): iterable
    {
        return $this->transformers;
    }

    /** @return int[] */
    public function getPriorities(): array
    {
        $transformers = [...$this->transformers];
        $priorites = array_map(
            static fn (NodeTransformer $transformer): int => $transformer->getPriority(),
            $transformers,
        );
        sort($priorites);
        $priorites = array_unique($priorites);

        return $priorites;
    }
}
