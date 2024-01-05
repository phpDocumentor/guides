<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Node;

use function assert;

class SubInternalMenuEntryNodeTransformer extends AbstractMenuEntryNodeTransformer
{
    use MenuEntryManagement;
    use SubSectionHierarchyHandler;

    // Setting a default level prevents PHP errors in case of circular references
    private const DEFAULT_MAX_LEVELS = 10;

    public function supports(Node $node): bool
    {
        return $node instanceof InternalMenuEntryNode;
    }

    /** @return list<MenuEntryNode> */
    protected function handleMenuEntry(MenuNode $currentMenu, MenuEntryNode $entryNode, CompilerContext $compilerContext): array
    {
        assert($entryNode instanceof InternalMenuEntryNode);
        $maxDepth = (int) $currentMenu->getOption('maxdepth', self::DEFAULT_MAX_LEVELS);
        $documentEntryOfMenuEntry = $compilerContext->getProjectNode()->getDocumentEntry($entryNode->getUrl());
        $this->addSubEntries($currentMenu, $compilerContext, $entryNode, $documentEntryOfMenuEntry, $entryNode->getLevel() + 1, $maxDepth);

        return [$entryNode];
    }

    public function getPriority(): int
    {
        // After MenuEntries are resolved
        return 3000;
    }

    private function addSubEntries(
        MenuNode $currentMenu,
        CompilerContext $compilerContext,
        InternalMenuEntryNode $sectionMenuEntry,
        DocumentEntryNode $documentEntry,
        int $currentLevel,
        int $maxDepth,
    ): void {
        if ($maxDepth < $currentLevel) {
            return;
        }

        foreach ($documentEntry->getChildren() as $subDocumentEntryNode) {
            $subMenuEntry = new InternalMenuEntryNode(
                $subDocumentEntryNode->getFile(),
                $subDocumentEntryNode->getTitle(),
                [],
                false,
                $currentLevel,
                '',
                self::isInRootline($subDocumentEntryNode, $compilerContext->getDocumentNode()->getDocumentEntry()),
                self::isCurrent($subDocumentEntryNode, $compilerContext->getDocumentNode()->getFilePath()),
            );

            if (!$currentMenu->hasOption('titlesonly') && $maxDepth - $currentLevel + 1 > 1) {
                $this->addSubSectionsToMenuEntries($subDocumentEntryNode, $subMenuEntry, $maxDepth - $currentLevel + 2);
            }

            $sectionMenuEntry->addMenuEntry($subMenuEntry);
            $this->addSubEntries($currentMenu, $compilerContext, $subMenuEntry, $subDocumentEntryNode, $currentLevel + 1, $maxDepth);
        }
    }
}
