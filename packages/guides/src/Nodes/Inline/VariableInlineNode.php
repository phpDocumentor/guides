<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

use phpDocumentor\Guides\Nodes\Node;

class VariableInlineNode extends InlineMarkupToken
{
    public const TYPE = 'variable';

    private Node $child;

    public function __construct(string $value)
    {
        parent::__construct(self::TYPE, $value);
    }

    public function getChild(): Node
    {
        return $this->child;
    }

    public function setChild(Node $child): void
    {
        $this->child = $child;
    }
}
