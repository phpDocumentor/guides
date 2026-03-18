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

namespace phpDocumentor\Guides\RestructuredText\Nodes;

use function class_alias;
use function class_exists;

final class TabNode extends AbstractTabNode
{
}


if (!class_exists(\phpDocumentor\Guides\Bootstrap\Nodes\TabNode::class, false)) {
    class_alias(TabNode::class, \phpDocumentor\Guides\Bootstrap\Nodes\TabNode::class);
}
