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

namespace phpDocumentor\Guides\MachineReadable\Toc;

use JsonSerializable;

/**
 * One page of "toc.json": where it is, which files it was rendered to, its
 * title and its anchor, and the pages its table of contents leads to.
 *
 * Keys a theme adds go into {@see PageDescriptor::setExtra()}; they are written
 * after the anchor and before the pages, so that the key a reader scrolls past
 * last is the one that opens a subtree.
 */
final class PageDescriptor implements JsonSerializable
{
    /** @var array<string, mixed> */
    private array $extra = [];

    /** @var list<PageDescriptor> */
    private array $pages = [];

    /** @param array<string, string> $outputFiles the file per output format, keyed by the extension it writes */
    public function __construct(
        private string $path,
        private array $outputFiles,
        private string $title,
        private string $anchor,
        private bool $orphan = false,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /** @return array<string, string> */
    public function getOutputFiles(): array
    {
        return $this->outputFiles;
    }

    /** @param array<string, string> $outputFiles */
    public function setOutputFiles(array $outputFiles): self
    {
        $this->outputFiles = $outputFiles;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAnchor(): string
    {
        return $this->anchor;
    }

    public function setAnchor(string $anchor): self
    {
        $this->anchor = $anchor;

        return $this;
    }

    /** Whether no table of contents leads to the page. */
    public function isOrphan(): bool
    {
        return $this->orphan;
    }

    /** @return array<string, mixed> */
    public function getExtra(): array
    {
        return $this->extra;
    }

    public function setExtra(string $key, mixed $value): self
    {
        $this->extra[$key] = $value;

        return $this;
    }

    /** @return list<PageDescriptor> */
    public function getPages(): array
    {
        return $this->pages;
    }

    public function addPage(PageDescriptor $page): self
    {
        $this->pages[] = $page;

        return $this;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        $page = [];
        if ($this->orphan) {
            $page['orphan'] = true;
        }

        $page = [
            ...$page,
            'path' => $this->path,
            ...$this->outputFiles,
            'title' => $this->title,
            'anchor' => $this->anchor,
            ...$this->extra,
        ];

        // Left out rather than written as an empty list: most pages of a large
        // manual are leaves, and the TYPO3 Core Changelog alone would spend
        // 50 KB saying so 3875 times.
        if ($this->pages !== []) {
            $page['pages'] = $this->pages;
        }

        return $page;
    }
}
