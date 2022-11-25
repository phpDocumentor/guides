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

namespace phpDocumentor\Guides;

use phpDocumentor\Guides\Meta\Entry;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Nodes\TocNode;

final class Metas
{
    /** @var Entry[] */
    private array $entries;

    /** @var string[] */
    private array $parents = [];

    /**
     * @param Entry[] $entries
     */
    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    /**
     * @return Entry[]
     */
    public function getAll(): array
    {
        return $this->entries;
    }

    /**
     * @param TitleNode[] $titles
     * @param TocNode[] $tocs
     * @param string[] $depends
     */
    public function set(
        string $file,
        string $url,
        ?TitleNode $title,
        array $titles,
        array $tocs,
        int $mtime,
        array $depends
    ): void {
        foreach ($tocs as $toc) {
            foreach ($toc as $child) {
                $this->parents[$child] = $file;

                if (!isset($this->entries[$child])) {
                    continue;
                }

                $this->entries[$child]->setParent($file);
            }
        }

        $this->entries[$file] = new Entry(
            $file,
            $url,
            $title ?? new TitleNode(new SpanNode('<unknown>'), 0),
            $titles,
            $tocs,
            $depends,
            $mtime
        );

        if (!isset($this->parents[$file])) {
            return;
        }

        $this->entries[$file]->setParent($this->parents[$file]);
    }

    public function get(string $url): ?Entry
    {
        if (isset($this->entries[$url])) {
            return $this->entries[$url];
        }

        return null;
    }

    /**
     * @param Entry[] $metaEntries
     */
    public function setMetaEntries(array $metaEntries): void
    {
        $this->entries = $metaEntries;
    }
}
