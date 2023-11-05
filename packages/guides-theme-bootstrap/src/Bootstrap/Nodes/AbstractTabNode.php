<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Bootstrap\Nodes;

use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;

abstract class AbstractTabNode extends GeneralDirectiveNode
{
    /** @param list<Node> $value */
    public function __construct(
        protected readonly string $name,
        protected readonly string $plainContent,
        protected readonly InlineCompoundNode $content,
        private readonly string $key,
        private bool $active,
        array $value = [],
    ) {
        parent::__construct($name, $plainContent, $content, $value);
    }

    public function getKey(): string
    {
        return $this->key;
    }
    
    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
