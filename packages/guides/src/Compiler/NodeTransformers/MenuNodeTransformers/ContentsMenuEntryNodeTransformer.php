<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Nodes\Menu\ContentMenuNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\SectionMenuEntryNode;
use phpDocumentor\Guides\Nodes\Node;

use function assert;

use const PHP_INT_MAX;

final class ContentsMenuEntryNodeTransformer extends AbstractMenuEntryNodeTransformer
{
    use SubSectionHierarchyHandler;

    private const DEFAULT_MAX_LEVELS = PHP_INT_MAX;

    public function supports(Node $node): bool
    {
        return $node instanceof SectionMenuEntryNode;
    }

    /** @return list<MenuEntryNode> */
    protected function handleMenuEntry(MenuNode $currentMenu, MenuEntryNode $entryNode, CompilerContext $compilerContext): array
    {
        if (!$currentMenu instanceof ContentMenuNode) {
            return [$entryNode];
        }

        assert($entryNode instanceof SectionMenuEntryNode);
        $depth = (int) $currentMenu->getOption('depth', self::DEFAULT_MAX_LEVELS - 1) + 1;
        $documentEntry = $compilerContext->getDocumentNode()->getDocumentEntry();
        $newEntryNode = new SectionMenuEntryNode(
            $documentEntry->getFile(),
            $entryNode->getValue() ?? $documentEntry->getTitle(),
            1,
        );
        $this->addSubSectionsToMenuEntries($documentEntry, $newEntryNode, $depth);

        return $newEntryNode->getSections();
    }

    public function getPriority(): int
    {
        // After DocumentEntryTransformer
        return 4500;
    }
}
