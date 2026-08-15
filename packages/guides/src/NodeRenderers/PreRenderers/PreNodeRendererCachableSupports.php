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

namespace phpDocumentor\Guides\NodeRenderers\PreRenderers;

/**
 * Lets a pre-renderer declare that its supports() depends on the node class alone.
 *
 * `PreNodeRenderer::supports()` receives a node instance and may inspect its state, so the answer can
 * differ between two nodes of the same class. A pre-renderer that only looks at the class can say so
 * here, and `PreNodeRendererFactory` may then decide once per class instead of once per node.
 */
interface PreNodeRendererCachableSupports
{
    /** Whether supports() gives the same answer for every node of a class. */
    public function cacheSupport(): bool;
}
