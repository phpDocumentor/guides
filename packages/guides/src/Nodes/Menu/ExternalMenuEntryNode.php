<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Menu;

use phpDocumentor\Guides\Nodes\TitleNode;

final class ExternalMenuEntryNode extends MenuEntryNode
{
    public function __construct(
        string $url,
        TitleNode $title,
        int $level = 1,
    ) {
        parent::__construct($url, $title, $level);
    }

    public function __toString(): string
    {
        return $this->getUrl();
    }
}
