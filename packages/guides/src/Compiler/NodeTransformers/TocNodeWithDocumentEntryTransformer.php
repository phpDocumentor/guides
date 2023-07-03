<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\SectionEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;

use function array_pop;
use function assert;
use function count;
use function explode;
use function implode;
use function preg_match;
use function str_replace;
use function str_starts_with;

/** @implements NodeTransformer<MenuNode> */
class TocNodeWithDocumentEntryTransformer implements NodeTransformer
{
    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        if (!$node instanceof TocNode) {
            return $node;
        }

        $files = $node->getFiles();
        $glob = $node->hasOption('glob');

        $documentEntries = $compilerContext->getProjectNode()->getAllDocumentEntries();
        $currentPath = $compilerContext->getDocumentNode()->getFilePath();
        $documentEntriesInTree = [];
        $menuEntries = [];

        foreach ($files as $file) {
            foreach ($documentEntries as $documentEntry) {
                if (!$this->isEqualAbsolutePath($documentEntry, $file, $currentPath, $glob) && !$this->isEqualRelativePath($documentEntry, $file, $currentPath, $glob)) {
                    continue;
                }

                $documentEntriesInTree[] = $documentEntry;
                $menuEntry = new MenuEntryNode($documentEntry->getFile(), $documentEntry->getTitle(), [], false, 1);
                if (!$node->hasOption('titlesonly') && count($documentEntry->getSections()) > 0) {
                    foreach ($documentEntry->getSections() as $section) {
                        // We do not add the main section as it repeats the document title
                        if (count($section->getChildren()) <= 0) {
                            continue;
                        }

                        foreach ($section->getChildren() as $subSectionEntryNode) {
                            assert($subSectionEntryNode instanceof SectionEntryNode);
                            $currentLevel = $menuEntry->getLevel() + 1;
                            $sectionMenuEntry = new MenuEntryNode($documentEntry->getFile(), $subSectionEntryNode->getTitle(), [], false, $currentLevel, $subSectionEntryNode->getId());
                            $menuEntry->addSection($sectionMenuEntry);
                            $this->addSubSections($sectionMenuEntry, $subSectionEntryNode, $documentEntry, $currentLevel);
                        }
                    }
                }

                $menuEntries[] = $menuEntry;
                if (!$glob) {
                    // If glob is not set, there may only be one result per listed file
                    break;
                }
            }
        }

        $node = $node->withMenuEntries($menuEntries);

        return $node;
    }

    private function addSubSections(MenuEntryNode $sectionMenuEntry, SectionEntryNode $sectionEntryNode, DocumentEntryNode $documentEntry, int $currentLevel): void
    {
        foreach ($sectionEntryNode->getChildren() as $subSectionEntryNode) {
            $sectionMenuEntry = new MenuEntryNode($documentEntry->getFile(), $subSectionEntryNode->getTitle(), [], false, $currentLevel, $subSectionEntryNode->getId());
            $sectionMenuEntry->addSection($sectionMenuEntry);
        }
    }

    public function supports(Node $node): bool
    {
        return $node instanceof MenuNode;
    }

    public function getPriority(): int
    {
        // After DocumentEntryTransformer
        return 4500;
    }

    private function isEqualAbsolutePath(DocumentEntryNode $documentEntry, string $file, string $currentPath, bool $glob): bool
    {
        if ($file === '/' . $documentEntry->getFile()) {
            return true;
        }

        if ($glob && $documentEntry->getFile() !== $currentPath) {
            $file = str_replace('*', '[a-zA-Z0-9]*', $file);
            $pattern = '`^' . $file . '$`';

            return preg_match($pattern, '/' . $documentEntry->getFile()) > 0;
        }

        return false;
    }

    private function isEqualRelativePath(DocumentEntryNode $documentEntry, string $file, string $currentPath, bool $glob): bool
    {
        if (str_starts_with($file, '/')) {
            return false;
        }

        $current = explode('/', $currentPath);
        array_pop($current);
        $current[] = $file;
        $absolute = implode('/', $current);

        if ($absolute === $documentEntry->getFile()) {
            return true;
        }

        if ($glob && $documentEntry->getFile() !== $currentPath) {
            $file = str_replace('*', '[a-zA-Z0-9]*', $file);
            $pattern = '`^' . $file . '$`';

            return preg_match($pattern, $documentEntry->getFile()) > 0;
        }

        return false;
    }
}
