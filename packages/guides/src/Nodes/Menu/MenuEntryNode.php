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

use phpDocumentor\Guides\Nodes\AbstractNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use Stringable;

/** @extends AbstractNode<TitleNode|null> */
abstract class MenuEntryNode extends AbstractNode implements Stringable
{
    public function __construct(
        private readonly string $url,
        TitleNode|null $title,
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
