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
 * Contains nodes inserted into a document by an include directive.
 *
 * The parser expands this node into its parent so included content behaves as
 * if it had been written directly at the include location.
 *
 * @extends CompoundNode<Node>
 */
final class IncludedContentNode extends CompoundNode
{
}
