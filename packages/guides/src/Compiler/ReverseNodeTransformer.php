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

/**
 * Reverse NodeTransformers are applied with child nodes first, then the parent node.
 *
 * This is useful for NodeTransformers that need to know the state of the child nodes before transforming the parent node.
 */
interface ReverseNodeTransformer extends NodeTransformer
{
}
