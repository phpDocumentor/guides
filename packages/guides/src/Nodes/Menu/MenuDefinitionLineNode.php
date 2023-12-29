<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Menu;

use phpDocumentor\Guides\Nodes\AbstractNode;
use Stringable;

/**
 * This node contains the result of parsing the menu entries for a menu.
 *
 * @extends AbstractNode<String>
 */
final class MenuDefinitionLineNode extends AbstractNode implements Stringable
{
    public function __construct(
        private readonly string $reference,
        private readonly string|null $title = null,
    ) {
        $this->value = $reference;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getTitle(): string|null
    {
        return $this->title;
    }

    public function __toString(): string
    {
        return $this->reference;
    }
}
