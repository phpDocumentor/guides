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
final class FigureNode extends AbstractNode
{
    public function __construct(ImageNode $image, protected Node|null $document = null)
    {
        $this->value = $image;
    }

    public function getImage(): ImageNode
    {
        return $this->value;
    }

    public function getDocument(): Node|null
    {
        return $this->document;
    }

    public function withImage(ImageNode $image): self
    {
        $result = clone $this;
        $result->value = $image;

        return $result;
    }

    public function withDocument(Node|null $document): self
    {
        $result = clone $this;
        $result->document = $document;

        return $result;
    }
}
