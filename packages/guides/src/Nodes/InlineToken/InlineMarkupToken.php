<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

use phpDocumentor\Guides\Nodes\AbstractNode;

/** @extends AbstractNode<String> */
abstract class InlineMarkupToken extends AbstractNode
{
    public function __construct(private readonly string $type, private readonly string $id, string $value = '')
    {
        $this->value = $value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
