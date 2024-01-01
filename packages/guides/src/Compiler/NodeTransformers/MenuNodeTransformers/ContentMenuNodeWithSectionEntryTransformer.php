<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\SectionEntryNode;
use phpDocumentor\Guides\Nodes\Menu\ContentMenuNode;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use function assert;

/** @implements NodeTransformer<TocNode> */
class ContentMenuNodeWithSectionEntryTransformer implements NodeTransformer
{
    private const DEFAULT_MAX_LEVELS = PHP_INT_MAX;

    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        if (!$node instanceof ContentMenuNode) {
            return $node;
        }

        $depth = (int) $node->getOption('depth', self::DEFAULT_MAX_LEVELS);
        $documentEntry = $compilerContext->getDocumentNode()->getDocumentEntry();

        $menuEntries = [];
        foreach ($documentEntry->getSections() as $section) {
            // We do not add the main section as it repeats the document title
            foreach ($section->getChildren() as $subSectionEntryNode) {
                assert($subSectionEntryNode instanceof SectionEntryNode);
                $sectionMenuEntry = new InternalMenuEntryNode(
                    $documentEntry->getFile(),
                    $subSectionEntryNode->getTitle(),
                    [],
                    false,
                    1,
                    $subSectionEntryNode->getId(),
                );
                $menuEntries[] = $sectionMenuEntry;
                $this->addSubSections($sectionMenuEntry, $subSectionEntryNode, $documentEntry, 1, $depth);
            }
        }

        $node = $node->withMenuEntries($menuEntries);

        return $node;
    }

    private function addSubSections(
        InternalMenuEntryNode $sectionMenuEntry,
        SectionEntryNode $sectionEntryNode,
        DocumentEntryNode $documentEntry,
        int $currentLevel,
        int $maxLevel,
    ): void {
        if ($currentLevel >= $maxLevel) {
            return;
        }

        foreach ($sectionEntryNode->getChildren() as $subSectionEntryNode) {
            $subSectionMenuEntry = new InternalMenuEntryNode(
                $documentEntry->getFile(),
                $subSectionEntryNode->getTitle(),
                [],
                false,
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

    public function supports(Node $node): bool
    {
        return false;
    }

    public function getPriority(): int
    {
        // After DocumentEntryTransformer
        return 4500;
    }
}
