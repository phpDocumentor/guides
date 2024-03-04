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

namespace phpDocumentor\Guides\Nodes\DocumentTree;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\TitleNode;

/** @extends EntryNode<DocumentNode> */
final class DocumentEntryNode extends EntryNode
{
    /** @var array<DocumentEntryNode|ExternalEntryNode> */
    private array $entries = [];
    /** @var SectionEntryNode[]  */
    private array $sections = [];

    public function __construct(
        private readonly string $file,
        private readonly TitleNode $titleNode,
        private readonly bool $isRoot = false,
    ) {
    }

    public function getTitle(): TitleNode
    {
        return $this->titleNode;
    }

    public function addChild(DocumentEntryNode|ExternalEntryNode $child): void
    {
        $this->entries[] = $child;
    }

    /**
     * @return array<DocumentEntryNode>
     */
    public function getChildren(): array
    {
        // Filter the entries array to only include DocumentEntryNode instances
        $documentEntries = array_filter($this->entries, function ($entry) {
            return $entry instanceof DocumentEntryNode;
        });

        // Re-index the array to maintain numeric keys
        return array_values($documentEntries);
    }


    /** @return array<DocumentEntryNode|ExternalEntryNode> */
    public function getMenuEntries(): array
    {
        return $this->entries;
    }

    /** @return SectionEntryNode[] */
    public function getSections(): array
    {
        return $this->sections;
    }

    public function addSection(SectionEntryNode $section): void
    {
        $this->sections[] = $section;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function isRoot(): bool
    {
        return $this->isRoot;
    }
}
