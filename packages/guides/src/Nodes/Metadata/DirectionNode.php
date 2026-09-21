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

use phpDocumentor\Guides\Nodes\TextDirection;

/**
 * Sets the text direction of the document, e.g. `:dir: rtl`, used as the HTML dir attribute.
 */
final class DirectionNode extends MetadataNode
{
    public function __construct(private readonly TextDirection $direction)
    {
        parent::__construct($direction->value);
    }

    public function getDirection(): TextDirection
    {
        return $this->direction;
    }
}
