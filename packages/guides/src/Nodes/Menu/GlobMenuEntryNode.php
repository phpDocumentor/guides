<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

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
