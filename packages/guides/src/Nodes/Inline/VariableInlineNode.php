<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

use phpDocumentor\Guides\Nodes\Node;

class VariableInlineNode extends InlineNode
{
    final public const TYPE = 'variable';

    private Node|null $child = null;

    public function __construct(string $value)
    {
        parent::__construct(self::TYPE, $value);
    }

    public function getChild(): Node
    {
        if ($this->child === null) {
            return new PlainTextInlineNode('|' . $this->value . '|');
        }

        return $this->child;
    }

    public function setChild(Node $child): void
    {
        $this->child = $child;
    }
}
