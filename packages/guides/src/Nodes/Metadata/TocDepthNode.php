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

namespace phpDocumentor\Guides\Nodes\Metadata;

/**
 * The maximum depth for a table of contents of this file.
 *
 * .. note::
 *    This metadata effects to the depth of local toctree. But it does not effect to the depth of global
 *    toctree. So this would not be change the sidebar of some themes which uses global one.
 */
final class TocDepthNode extends MetadataNode
{
    public function __construct(int $value)
    {
        parent::__construct((string) $value);
    }
}
