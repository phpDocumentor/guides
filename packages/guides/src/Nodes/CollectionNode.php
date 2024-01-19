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
 * A collection node is a node that renders all its children without adding
 * a wrap. It is used by directives, that change sub-nodes without
 * having an output of their own, like the class-directive.
 *
 * @extends CompoundNode<Node>
 */
final class CollectionNode extends CompoundNode
{
}
