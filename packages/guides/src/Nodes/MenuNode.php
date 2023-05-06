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

namespace phpDocumentor\Guides\Nodes;

use phpDocumentor\Guides\Nodes\TableOfContents\Entry;

use const PHP_INT_MAX;

/**
 * @link https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#table-of-contents
 *
 * @extends CompoundNode<Node>
 */
abstract class MenuNode extends CompoundNode
{
    protected const DEFAULT_DEPTH = PHP_INT_MAX;

    /** @var Entry[] */
    private array $entries = [];

    /** @param string[] $files */
    public function __construct(private readonly array $files)
    {
        parent::__construct();
    }

    /** @return string[] */
    public function getFiles(): array
    {
        return $this->files;
    }

    abstract public function getDepth(): int;

    /** @param Entry[] $entries */
    public function withEntries(array $entries): self
    {
        $that = clone $this;
        $that->entries = $entries;

        return $that;
    }

    /** @return Entry[] */
    public function getEntries(): array
    {
        return $this->entries;
    }

    abstract public function isPageLevelOnly(): bool;
}
