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

namespace phpDocumentor\Guides\Bootstrap\Nodes;

use Doctrine\Deprecations\Deprecation;
use phpDocumentor\Guides\RestructuredText\Nodes\TabNode as RstTabNode;

use function class_exists;

Deprecation::trigger(
    'phpDocumentor/guides-theme-bootstrap',
    'https://github.com/phpDocumentor/guides/issues/1320',
    'The "%s" class is deprecated, use "%s" instead.',
    TabNode::class,
    RstTabNode::class,
);

class_exists(RstTabNode::class);

// @phpstan-ignore if.alwaysFalse
if (false) {
    final class TabNode
    {
    }
}
