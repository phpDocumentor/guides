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
