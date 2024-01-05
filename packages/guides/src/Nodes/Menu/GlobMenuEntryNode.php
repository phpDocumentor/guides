<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Menu;

/**
 * Represents a menu entry that can be replaced by multiple InternalMenuEntryNodes
 * during compilation. The url contains a string here. It cannot be rendered.
 */
final class GlobMenuEntryNode extends MenuEntryNode
{
    public function __construct(
        string $url,
        int $level = 1,
    ) {
        parent::__construct($url, null, $level);
    }

    public function __toString(): string
    {
        return $this->getUrl();
    }
}
