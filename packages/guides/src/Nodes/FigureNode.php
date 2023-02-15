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

/** @extends AbstractNode<ImageNode> */
class FigureNode extends AbstractNode
{
    protected ?Node $document;

    public function __construct(ImageNode $image, ?Node $document = null)
    {
        $this->value = $image;
        $this->document = $document;
    }

    public function getImage(): ImageNode
    {
        return $this->value;
    }

    public function getDocument(): ?Node
    {
        return $this->document;
    }
}
