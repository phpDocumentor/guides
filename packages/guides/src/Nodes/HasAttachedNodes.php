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

namespace phpDocumentor\Guides\Nodes;

/**
 * Implement this interface on a Node that keeps nodes in properties of its own,
 * beside its children, like the term of a definition-list item or the caption
 * of a code block. Node transformers visit these attached nodes as they visit
 * the children, so a role in a term is transformed like the same role in a
 * paragraph.
 *
 * A transformer can change what is inside an attached node, the change is
 * written into the attached node itself. It cannot replace or remove the
 * attached node.
 */
interface HasAttachedNodes
{
    /**
     * The attached nodes in document order, each under a name that is unique
     * within this node. Leave out a property that holds no node.
     *
     * @return array<string, Node>
     */
    public function getAttachedNodes(): array;
}
