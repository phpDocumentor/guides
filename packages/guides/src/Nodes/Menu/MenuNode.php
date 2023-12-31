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
use phpDocumentor\Guides\Nodes\Node;

use const PHP_INT_MAX;

/**
 * @link https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#table-of-contents
 *
 * @extends CompoundNode<Node>
 */
abstract class MenuNode extends CompoundNode
{
    private InlineCompoundNode|null $caption = null;
    protected const DEFAULT_DEPTH = PHP_INT_MAX;

    /** @var MenuEntryNode[] */
    private array $menuEntries = [];

    /** @param MenuDefinitionLineNode[] $parsedMenuEntryNodes */
    public function __construct(private readonly array $parsedMenuEntryNodes)
    {
        parent::__construct();
    }

    /** @return MenuDefinitionLineNode[] */
    public function getParsedMenuEntryNodes(): array
    {
        return $this->parsedMenuEntryNodes;
    }

    abstract public function getDepth(): int;

    /** @param MenuEntryNode[] $menuEntries */
    public function withMenuEntries(array $menuEntries): self
    {
        $that = clone $this;
        $that->menuEntries = $menuEntries;

        return $that;
    }

    /** @return MenuEntryNode[] */
    public function getMenuEntries(): array
    {
        return $this->menuEntries;
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
}
