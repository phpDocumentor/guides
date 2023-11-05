<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Bootstrap\Nodes;

use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;

final class TabNode extends AbstractTabNode
{
    /** @param list<Node> $value */
    public function __construct(
        string $name,
        string $plainContent,
        InlineCompoundNode $content,
        string $key,
        bool $active,
        array $value = [],
    ) {
        parent::__construct($name, $plainContent, $content, $key, $active, $value);
    }
}
