<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

use phpDocumentor\Guides\Nodes\AbstractNode;

/** @extends AbstractNode<String> */
abstract class InlineNode extends AbstractNode
{
    public function __construct(private readonly string $type, string $value = '')
    {
        $this->value = $value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
