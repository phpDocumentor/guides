<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\NavMenuNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;

/** @implements NodeTransformer<MenuNode> */
class MenuNodeAddSubDocumentsTransformer implements NodeTransformer
{
    // Setting a default level prevents PHP errors in case of circular references
    private const DEFAULT_MAX_LEVELS = 10;

    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        if (!$node instanceof TocNode && !$node instanceof NavMenuNode) {
            return $node;
        }

        $maxDepth = (int) $node->getOption('maxdepth', self::DEFAULT_MAX_LEVELS);


        foreach ($node->getMenuEntries() as $menuEntry) {
            $documentEntryOfMenuEntry = $compilerContext->getProjectNode()->getDocumentEntry($menuEntry->getUrl());
            $this->addSubEntries($compilerContext, $menuEntry, $documentEntryOfMenuEntry, $menuEntry->getLevel() + 1, $maxDepth);
        }

        return $node;
    }

    private function addSubEntries(
        CompilerContext $compilerContext,
        MenuEntryNode $sectionMenuEntry,
        DocumentEntryNode $documentEntry,
        int $currentLevel,
        int $maxDepth,
    ): void {
        if ($maxDepth < $currentLevel) {
            return;
        }
        
        foreach ($documentEntry->getChildren() as $subDocumentEntryNode) {
            $subMenuEntry = new MenuEntryNode(
                $subDocumentEntryNode->getFile(),
                $subDocumentEntryNode->getTitle(),
                [],
                false,
                $currentLevel,
                '',
                self::isInRootline($subDocumentEntryNode, $compilerContext->getDocumentNode()->getDocumentEntry()),
                self::isCurrent($subDocumentEntryNode, $compilerContext->getDocumentNode()->getFilePath()),
            );
            $sectionMenuEntry->addMenuEntry($subMenuEntry);
            $this->addSubEntries($compilerContext, $subMenuEntry, $subDocumentEntryNode, $currentLevel + 1, $maxDepth);
        }
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TocNode || $node instanceof NavMenuNode;
    }

    private function isInRootline(DocumentEntryNode $menuEntry, DocumentEntryNode|null $currentDoc): bool
    {
        if ($currentDoc === null) {
            return false;
        }

        return $menuEntry->getFile() === $currentDoc->getFile()
            || self::isInRootline($menuEntry, $currentDoc->getParent());
    }

    private function isCurrent(DocumentEntryNode $menuEntry, string $currentPath): bool
    {
        return $menuEntry->getFile() === $currentPath;
    }

    public function getPriority(): int
    {
        // After TocNodeWithDocumentEntryTransformer
        return 4000;
    }
}
