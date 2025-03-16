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

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;

use const PHP_INT_MAX;

/**
 * @link https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#table-of-contents
 *
 * @extends CompoundNode<MenuEntryNode>
 */
abstract class MenuNode extends CompoundNode
{
    private InlineCompoundNode|null $caption = null;
    private bool $reversed = false;
    protected const DEFAULT_DEPTH = PHP_INT_MAX;

    /** @param MenuEntryNode[] $menuEntries */
    public function __construct(array $menuEntries)
    {
        parent::__construct($menuEntries);
    }

    abstract public function getDepth(): int;

    /** @return MenuEntryNode[] */
    public function getMenuEntries(): array
    {
        return $this->value;
    }

    abstract public function isPageLevelOnly(): bool;

    public function getCaption(): InlineCompoundNode|null
    {
        return $this->caption;
    }

    public function withCaption(InlineCompoundNode|null $caption): static
    {
        $that = clone $this;
        $that->caption = $caption;

        return $that;
    }

    public function isReversed(): bool
    {
        return $this->reversed;
    }

    public function withReversed(bool $reversed): MenuNode
    {
        $that = clone $this;
        $that->reversed = $reversed;

        return $that;
    }
}
