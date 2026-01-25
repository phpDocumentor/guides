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

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Nodes\Node;

use function is_callable;
use function is_iterable;

/**
 * Provides recursive node traversal functionality for compiler passes.
 */
// phpcs:ignore SlevomatCodingStandard.Classes.SuperfluousTraitNaming.SuperfluousSuffix
trait NodeTraversalTrait
{
    /**
     * Maximum recursion depth to prevent stack overflow from maliciously
     * crafted documents with deeply nested structures.
     *
     * Note: Defined as a method instead of a constant for PHP 8.1 compatibility
     * (constants in traits require PHP 8.2+).
     */
    private function getMaxTraversalDepth(): int
    {
        return 100;
    }

    /**
     * Traverse all nodes recursively, including nested children.
     *
     * Includes depth limiting to prevent stack overflow on deeply nested documents.
     *
     * @param iterable<Node> $nodes
     * @param callable(Node): void $callback
     * @param int $depth Current recursion depth (internal use)
     */
    private function traverseNodes(iterable $nodes, callable $callback, int $depth = 0): void
    {
        if ($depth > $this->getMaxTraversalDepth()) {
            // Security: Silently stop traversal to prevent stack overflow from
            // maliciously crafted deeply nested documents. Nodes beyond this
            // depth will not have their callbacks invoked. This is intentional
            // behavior - 100 levels is sufficient for any legitimate document.
            return;
        }

        foreach ($nodes as $node) {
            $callback($node);

            // Use is_callable to ensure the method is both present AND accessible
            // (method_exists alone returns true for private/protected methods)
            if (!is_callable([$node, 'getChildren'])) {
                continue;
            }

            $children = $node->getChildren();
            if (!is_iterable($children)) {
                continue;
            }

            $this->traverseNodes($children, $callback, $depth + 1);
        }
    }
}
