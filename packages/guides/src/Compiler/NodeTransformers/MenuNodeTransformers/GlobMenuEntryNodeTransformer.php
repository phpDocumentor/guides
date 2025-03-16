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

use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Nodes\Menu\GlobMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;

use function array_pop;
use function assert;
use function explode;
use function implode;
use function in_array;
use function is_string;
use function preg_match;
use function preg_replace;

final class GlobMenuEntryNodeTransformer extends AbstractMenuEntryNodeTransformer
{
    use MenuEntryManagement;
    use SubSectionHierarchyHandler;

    // Setting a default level prevents PHP errors in case of circular references
    private const DEFAULT_MAX_LEVELS = 10;

    /** @return list<MenuEntryNode> */
    protected function handleMenuEntry(MenuNode $currentMenu, MenuEntryNode $entryNode, CompilerContextInterface $compilerContext): array
    {
        assert($entryNode instanceof GlobMenuEntryNode);
        $maxDepth = (int) $currentMenu->getOption('maxdepth', self::DEFAULT_MAX_LEVELS);
        $documentEntries = $compilerContext->getProjectNode()->getAllDocumentEntries();
        $currentPath = $compilerContext->getDocumentNode()->getFilePath();
        $globExclude = explode(',', $currentMenu->getOption('globExclude') . '');
        $menuEntries = [];
        foreach ($documentEntries as $documentEntry) {
            if ($documentEntry->isOrphan()) {
                continue;
            }

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

            $titleNode = $documentEntry->getTitle();
            $navigationTitle =  $documentEntry->getAdditionalData('navigationTitle');
            if ($navigationTitle instanceof TitleNode) {
                $titleNode = $navigationTitle;
            }

            $documentEntriesInTree[] = $documentEntry;
            $newEntryNode = new InternalMenuEntryNode(
                $documentEntry->getFile(),
                $titleNode,
                [],
                false,
                1,
                '',
                self::isInRootline($documentEntry, $compilerContext->getDocumentNode()->getDocumentEntry()),
                self::isCurrent($documentEntry, $currentPath),
            );
            if (!$currentMenu->hasOption('titlesonly') && $maxDepth > 1) {
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
        // After GlobMenuEntryNodeTransformer
        // Before SubInternalMenuEntry
        return 4000;
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
            $file = preg_replace('/(?<!\*)\*(?!\*)/', '[^\/]*', $file);
            assert(is_string($file));
            $file = preg_replace('/\*{2}/', '.*', $file);
            $pattern = '`^' . $file . '$`';

            return preg_match($pattern, $prefix . $documentEntryFile) > 0;
        }

        return false;
    }
}
