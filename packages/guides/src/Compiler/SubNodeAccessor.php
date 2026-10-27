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

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Nodes\Node;

/**
 * Describes one edge type of the node tree: where child nodes of an owner are stored, and how
 * a child is replaced or removed. Nodes are immutable, so the modifying methods return the new owner.
 */
interface SubNodeAccessor
{
    /** @return array<array-key, Node> */
    public function items(Node $owner): array;

    public function replaceAt(Node $owner, int|string|null $key, Node $node): Node;

    public function removeAt(Node $owner, int|string|null $key): Node;
}
