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

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;

use function array_filter;
use function array_values;

/** @extends EntryNode<DocumentEntryNode|ExternalEntryNode> */
final class DocumentEntryNode extends EntryNode
{
    /** @var array<DocumentEntryNode|ExternalEntryNode> */
    private array $entries = [];
    /** @var SectionEntryNode[]  */
    private array $sections = [];

    /** @param array<string, Node> $additionalData */
    public function __construct(
        private readonly string $file,
        private readonly TitleNode $titleNode,
        private readonly bool $isRoot = false,
        private array $additionalData = [],
        private bool $orphan = false,
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

    /** @return array<DocumentEntryNode> */
    public function getChildren(): array
    {
        // Filter the entries array to only include DocumentEntryNode instances
        $documentEntries = array_filter($this->entries, static function ($entry) {
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

    /** @param array<DocumentEntryNode|ExternalEntryNode> $entries */
    public function setMenuEntries(array $entries): void
    {
        $this->entries = $entries;
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

    public function findSectionEntry(SectionNode $sectionNode): SectionEntryNode|null
    {
        foreach ($this->sections as $sectionEntryNode) {
            if ($sectionNode->getId() === $sectionEntryNode->getId()) {
                return $sectionEntryNode;
            }
        }

        foreach ($this->sections as $sectionEntryNode) {
            $subsection = $sectionEntryNode->findSectionEntry($sectionNode);
            if ($subsection !== null) {
                return $subsection;
            }
        }

        return null;
    }

    public function getAdditionalData(string $key): Node|null
    {
        return $this->additionalData[$key] ?? null;
    }

    public function isOrphan(): bool
    {
        return $this->orphan;
    }

    public function addAdditionalData(string $key, Node $value): void
    {
        $this->additionalData[$key] = $value;
    }
}
