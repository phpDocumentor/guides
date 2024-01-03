<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use Exception;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\SectionEntryNode;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\SectionMenuEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use Psr\Log\LoggerInterface;

use function assert;
use function count;
use function sprintf;
use function str_starts_with;

/** @implements NodeTransformer<Node> */
abstract class AbstractMenuEntryNodeTransformer implements NodeTransformer
{
    private MenuNode|null $currentMenu = null;

    public function __construct(
        protected readonly LoggerInterface $logger,
    ) {
    }

    final public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        if ($node instanceof MenuNode) {
            $this->currentMenu = $node;
        }

        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        if ($node instanceof MenuNode) {
            $this->currentMenu = null;

            return $node;
        }

        if ($this->currentMenu === null) {
            throw new Exception('A MenuEntryNode must be attached to a MenuNode');
        }

        assert($node instanceof MenuEntryNode);

        $menuEntries = $this->handleMenuEntry($this->currentMenu, $node, $compilerContext);

        if (count($menuEntries) === 0) {
            return null;
        }

        if (count($menuEntries) === 1) {
            return $menuEntries[0];
        }

        foreach ($menuEntries as $menuEntry) {
            $compilerContext->getShadowTree()->getParent()?->addChild($menuEntry);
        }

        return null;
    }

    /** @return list<MenuEntryNode> */
    abstract protected function handleMenuEntry(MenuNode $currentMenu, MenuEntryNode $node, CompilerContext $compilerContext): array;

    protected function addSubSectionsToMenuEntries(DocumentEntryNode $documentEntry, InternalMenuEntryNode|SectionMenuEntryNode $menuEntry, int $maxLevel): void
    {
        foreach ($documentEntry->getSections() as $section) {
            // We do not add the main section as it repeats the document title
            foreach ($section->getChildren() as $subSectionEntryNode) {
                assert($subSectionEntryNode instanceof SectionEntryNode);
                $currentLevel = $menuEntry->getLevel() + 1;
                $sectionMenuEntry = new SectionMenuEntryNode(
                    $documentEntry->getFile(),
                    $subSectionEntryNode->getTitle(),
                    [],
                    false,
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

    /** @param DocumentEntryNode[] $documentEntriesInTree */
    protected function attachDocumentEntriesToParents(
        array $documentEntriesInTree,
        CompilerContext $compilerContext,
        string $currentPath,
    ): void {
        foreach ($documentEntriesInTree as $documentEntryInToc) {
            if ($documentEntryInToc->isRoot() || $currentPath === $documentEntryInToc->getFile()) {
                // The root page may not be attached to any other
                continue;
            }

            if ($documentEntryInToc->getParent() !== null && $documentEntryInToc->getParent() !== $compilerContext->getDocumentNode()->getDocumentEntry()) {
                $this->logger->warning(sprintf(
                    'Document %s has been added to parents %s and %s. The `toctree` directive changes the '
                    . 'position of documents in the document tree. Use the `menu` directive to only display a menu without changing the document tree.',
                    $documentEntryInToc->getFile(),
                    $documentEntryInToc->getParent()->getFile(),
                    $compilerContext->getDocumentNode()->getDocumentEntry()->getFile(),
                ), $compilerContext->getLoggerInformation());
            }

            if ($documentEntryInToc->getParent() !== null) {
                continue;
            }

            $documentEntryInToc->setParent($compilerContext->getDocumentNode()->getDocumentEntry());
            $compilerContext->getDocumentNode()->getDocumentEntry()->addChild($documentEntryInToc);
        }
    }

    protected function isInRootline(DocumentEntryNode $menuEntry, DocumentEntryNode $currentDoc): bool
    {
        return $menuEntry->getFile() === $currentDoc->getFile()
            || ($currentDoc->getParent() !== null
                && self::isInRootline($menuEntry, $currentDoc->getParent()));
    }

    protected function isCurrent(DocumentEntryNode $menuEntry, string $currentPath): bool
    {
        return $menuEntry->getFile() === $currentPath;
    }

    protected static function isAbsoluteFile(string $expectedFile): bool
    {
        return str_starts_with($expectedFile, '/');
    }
}
