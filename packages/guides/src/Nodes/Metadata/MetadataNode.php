<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Metadata;

use phpDocumentor\Guides\Nodes\AbstractNode;

/** @extends AbstractNode<?string> */
abstract class MetadataNode extends AbstractNode
{
    public function __construct(string|null $value = null)
    {
        $this->setValue($value);
    }

    public function toString(): string
    {
        return $this->value ?? '';
    }
}
