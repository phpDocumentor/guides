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

namespace phpDocumentor\Guides\NodeRenderers;

use phpDocumentor\Guides\Nodes\Node;

final class LazyNodeRendererFactory implements NodeRendererFactory
{
    /** @var callable */
    private $factory;

    private NodeRendererFactory|null $innerFactory = null;

    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    public function get(Node $node): NodeRenderer
    {
        if ($this->innerFactory === null) {
            $this->innerFactory = ($this->factory)();
        }

        return $this->innerFactory->get($node);
    }
}
