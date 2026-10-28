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

/** A field holding nodes keyed by name.
 *
 * @template TOwner of Node
 * @template TChild
 * @extends CallableAccessor<TOwner, TChild>
 */
final class ListAccessor extends CallableAccessor
{
}
