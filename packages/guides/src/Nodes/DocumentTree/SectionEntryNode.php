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
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;

/** @extends EntryNode<DocumentNode> */
final class SectionEntryNode extends EntryNode
{
    /** @var SectionEntryNode[] */
    private array $children = [];

    public function __construct(private readonly TitleNode $title)
    {
    }

    public function getId(): string
    {
        return $this->title->getId();
    }

    public function getTitle(): TitleNode
    {
        return $this->title;
    }

    public function addChild(SectionEntryNode $child): void
    {
        $this->children[] = $child;
    }

    /** @return SectionEntryNode[] */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function findSectionEntry(SectionNode $sectionNode): SectionEntryNode|null
    {
        foreach ($this->children as $sectionEntryNode) {
            if ($sectionNode->getId() === $sectionEntryNode->getId()) {
                return $sectionEntryNode;
            }
        }

        foreach ($this->children as $sectionEntryNode) {
            $subsection = $sectionEntryNode->findSectionEntry($sectionNode);
            if ($subsection !== null) {
                return $subsection;
            }
        }

        return null;
    }
}
