<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

/** @extends AbstractNode<string> */
final class BreadCrumbNode extends AbstractNode
{
    public function __construct(string $value = '')
    {
        $this->value = $value;
    }
}
