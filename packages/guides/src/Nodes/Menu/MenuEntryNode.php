<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Menu;

use phpDocumentor\Guides\Nodes\AbstractNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use Stringable;

/** @extends AbstractNode<TitleNode> */
abstract class MenuEntryNode extends AbstractNode implements Stringable
{
    public function __construct(
        private readonly string $url,
        TitleNode $title,
        private readonly int $level = 1,
    ) {
        $this->value = $title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getLevel(): int
    {
        return $this->level;
    }
}
