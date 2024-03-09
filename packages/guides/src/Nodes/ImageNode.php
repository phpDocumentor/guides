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

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;

final class ImageNode extends TextNode
{
    public LinkInlineNode|null $target = null;

    public function getTarget(): LinkInlineNode|null
    {
        return $this->target;
    }

    public function setTarget(LinkInlineNode|null $target): void
    {
        $this->target = $target;
    }
}
