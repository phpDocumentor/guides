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

use LogicException;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;

use function assert;
use function is_int;

/** The default field: the ordinary children of a CompoundNode. */
final class ChildrenAccessor implements SubNodeAccessor
{
    /** @return array<Node> */
    public function items(Node $owner): array
    {
        return $owner instanceof CompoundNode ? $owner->getChildren() : [];
    }

    public function replaceAt(Node $owner, int|string|null $key, Node $node): Node
    {
        $compound = $this->compound($owner, $key);
        assert(is_int($key));

        return $compound->replaceNode($key, $node);
    }

    public function removeAt(Node $owner, int|string|null $key): Node
    {
        $compound = $this->compound($owner, $key);
        assert(is_int($key));

        return $compound->removeNode($key);
    }

    /** @return CompoundNode<Node> */
    private function compound(Node $owner, int|string|null $key): CompoundNode
    {
        if (!$owner instanceof CompoundNode || !is_int($key)) {
            throw new LogicException('Children can only be changed in a compound node by index');
        }

        return $owner;
    }
}
