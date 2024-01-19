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

namespace phpDocumentor\Guides\Graphs\Nodes;

use phpDocumentor\Guides\Nodes\TextNode;

final class UmlNode extends TextNode
{
    private string $caption = '';

    public function setCaption(string $caption): void
    {
        $this->caption = $caption;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }
}
