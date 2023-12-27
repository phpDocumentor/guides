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
use phpDocumentor\Guides\RenderContext;

/** @template T of Node */
interface NodeRenderer
{
    /** @param class-string<Node> $nodeFqcn */
    public function supports(string $nodeFqcn): bool;

    /** @param T $node */
    public function render(Node $node, RenderContext $renderContext): string;
}
