<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Nodes\Menu\GlobMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;

use function array_pop;
use function assert;
use function explode;
use function implode;
use function in_array;
use function preg_match;
use function str_replace;

final class GlobMenuEntryNodeTransformer extends AbstractMenuEntryNodeTransformer
{
    use MenuEntryManagement;
    use SubSectionHierarchyHandler;

    // Setting a default level prevents PHP errors in case of circular references
    private const DEFAULT_MAX_LEVELS = 10;

    /** @return list<MenuEntryNode> */
    protected function handleMenuEntry(MenuNode $currentMenu, MenuEntryNode $entryNode, CompilerContext $compilerContext): array
    {
        assert($entryNode instanceof GlobMenuEntryNode);
        $maxDepth = (int) $currentMenu->getOption('maxdepth', self::DEFAULT_MAX_LEVELS);
        $documentEntries = $compilerContext->getProjectNode()->getAllDocumentEntries();
        $currentPath = $compilerContext->getDocumentNode()->getFilePath();
        $globExclude = explode(',', $currentMenu->getOption('globExclude') . '');
        $menuEntries = [];
        foreach ($documentEntries as $documentEntry) {
            if (
                !self::matches($documentEntry->getFile(), $entryNode, $currentPath, $globExclude)
            ) {
                continue;
            }

            if ($currentMenu instanceof TocNode && self::isCurrent($documentEntry, $currentPath)) {
                // TocNodes do not select the current page in glob mode. In a menu we might want to display it
                continue;
            }

            foreach ($currentMenu->getChildren() as $currentMenuEntry) {
                if ($currentMenuEntry instanceof InternalMenuEntryNode && $currentMenuEntry->getUrl() === $documentEntry->getFile()) {
                    // avoid duplicates
                    continue 2;
                }
            }

            $documentEntriesInTree[] = $documentEntry;
            $newEntryNode = new InternalMenuEntryNode(
                $documentEntry->getFile(),
                $documentEntry->getTitle(),
                [],
                false,
                1,
                '',
                $this->isInRootline($documentEntry, $compilerContext->getDocumentNode()->getDocumentEntry()),
                $this->isCurrent($documentEntry, $currentPath),
            );
            if (!$currentMenu->hasOption('titlesonly')) {
                $this->addSubSectionsToMenuEntries($documentEntry, $newEntryNode, $maxDepth - 1);
            }

            if ($currentMenu instanceof TocNode) {
                $this->attachDocumentEntriesToParents($documentEntriesInTree, $compilerContext, $currentPath);
            }

            $menuEntries[] = $newEntryNode;
        }

        return $menuEntries;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof GlobMenuEntryNode;
    }

    public function getPriority(): int
    {
        // After DocumentEntryTransformer
        return 4500;
    }

    /** @param String[] $globExclude */
    private static function matches(string $actualFile, GlobMenuEntryNode $parsedMenuEntryNode, string $currentFile, array $globExclude): bool
    {
        $expectedFile = $parsedMenuEntryNode->getUrl();
        if (self::isAbsoluteFile($expectedFile)) {
            if ($expectedFile === '/' . $actualFile) {
                return true;
            }

            return self::isGlob($actualFile, $currentFile, $expectedFile, '/', $globExclude);
        }

        $current = explode('/', $currentFile);
        array_pop($current);
        $current[] = $expectedFile;
        $absoluteExpectedFile = implode('/', $current);

        if ($absoluteExpectedFile === $actualFile) {
            return true;
        }

        return self::isGlob($actualFile, $currentFile, $absoluteExpectedFile, '', $globExclude);
    }

    /** @param String[] $globExclude */
    private static function isGlob(string $documentEntryFile, string $currentPath, string $file, string $prefix, array $globExclude): bool
    {
        if (!in_array($documentEntryFile, $globExclude, true)) {
            $file = str_replace('*', '[^\/]*', $file);
            $pattern = '`^' . $file . '$`';

            return preg_match($pattern, $prefix . $documentEntryFile) > 0;
        }

        return false;
    }
}
