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

/**
 * @link https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#table-of-contents
 *
 * @extends CompoundNode<Node>
 */
class TocNode extends CompoundNode
{
    private const DEFAULT_DEPTH = 2;

    /** @var string[] */
    private array $files;

    /** @var Entry[] */
    private array $entries = [];

    /**
     * @param string[] $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;

        parent::__construct();
    }

    /**
     * @return string[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function getDepth(): int
    {
        if (is_int($this->getOption('depth'))) {
            return (int) $this->getOption('depth');
        }

        if (is_int($this->getOption('maxdepth'))) {
            return (int) $this->getOption('maxdepth');
        }

        return self::DEFAULT_DEPTH;
    }

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
}
