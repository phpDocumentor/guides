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

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\SectionEntryNode;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\SectionMenuEntryNode;

use function assert;

trait SubSectionHierarchyHandler
{
    private function addSubSectionsToMenuEntries(DocumentEntryNode $documentEntry, InternalMenuEntryNode|SectionMenuEntryNode $menuEntry, int $maxLevel): void
    {
        foreach ($documentEntry->getSections() as $section) {
            // We do not add the main section as it repeats the document title
            foreach ($section->getChildren() as $subSectionEntryNode) {
                assert($subSectionEntryNode instanceof SectionEntryNode);
                $currentLevel = $menuEntry->getLevel() + 1;
                $sectionMenuEntry = new SectionMenuEntryNode(
                    $documentEntry->getFile(),
                    $subSectionEntryNode->getTitle(),
                    $currentLevel,
                    $subSectionEntryNode->getId(),
                );
                $menuEntry->addSection($sectionMenuEntry);
                $this->addSubSections($sectionMenuEntry, $subSectionEntryNode, $documentEntry, $currentLevel, $maxLevel);
            }
        }
    }

    private function addSubSections(
        SectionMenuEntryNode $sectionMenuEntry,
        SectionEntryNode $sectionEntryNode,
        DocumentEntryNode $documentEntry,
        int $currentLevel,
        int $maxLevel,
    ): void {
        if ($currentLevel >= $maxLevel) {
            return;
        }

        foreach ($sectionEntryNode->getChildren() as $subSectionEntryNode) {
            $subSectionMenuEntry = new SectionMenuEntryNode(
                $documentEntry->getFile(),
                $subSectionEntryNode->getTitle(),
                $currentLevel,
                $subSectionEntryNode->getId(),
            );
            $sectionMenuEntry->addSection($subSectionMenuEntry);
            $this->addSubSections(
                $subSectionMenuEntry,
                $subSectionEntryNode,
                $documentEntry,
                $currentLevel + 1,
                $maxLevel,
            );
        }
    }
}
