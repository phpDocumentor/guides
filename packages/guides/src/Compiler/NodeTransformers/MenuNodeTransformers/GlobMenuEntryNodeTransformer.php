<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Nodes\Menu\GlobMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\NavMenuNode;
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
use function str_replace;
use function str_starts_with;

final class GlobMenuEntryNodeTransformer extends AbstractMenuEntryNodeTransformer
{
    protected function handleMenuEntry(MenuNode $currentMenu, MenuEntryNode $node, CompilerContext $compilerContext): array
    {
        assert($node instanceof GlobMenuEntryNode);
        $documentEntries = $compilerContext->getProjectNode()->getAllDocumentEntries();
        $currentPath = $compilerContext->getDocumentNode()->getFilePath();
        $globExclude = explode(',', $currentMenu->getOption('globExclude') . '');
        $menuEntries = [];
        foreach ($documentEntries as $documentEntry) {
            if (
                !self::isEqualAbsolutePath($documentEntry->getFile(), $node, $currentPath, $globExclude)
                && !self::isEqualRelativePath($documentEntry->getFile(), $node, $currentPath, $globExclude)
            ) {
                continue;
            }

            if ($currentMenu instanceof TocNode && self::isCurrent($documentEntry, $currentPath)) {
                // TocNodes do not select the current page in glob mode. In a menu we might want to display it
                continue;
            }

            $documentEntriesInTree[] = $documentEntry;
            $menuEntry = new InternalMenuEntryNode(
                $documentEntry->getFile(),
                is_string($node->getValue()) ? TitleNode::fromString($node->getValue()) : $documentEntry->getTitle(),
                [],
                false,
                1,
                '',
                $this->isInRootline($documentEntry, $compilerContext->getDocumentNode()->getDocumentEntry()),
                $this->isCurrent($documentEntry, $currentPath),
            );
            if (!$currentMenu->hasOption('titlesonly')) {
                $this->addSubSectionsToMenuEntries($documentEntry, $menuEntry);
            }

            if ($currentMenu instanceof TocNode) {
                $this->attachDocumentEntriesToParents($documentEntriesInTree, $compilerContext, $currentPath);
            }

            $menuEntries[] = $menuEntry;
        }

        return $menuEntries;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TocNode || $node instanceof NavMenuNode || $node instanceof GlobMenuEntryNode;
    }

    public function getPriority(): int
    {
        // After DocumentEntryTransformer
        return 4500;
    }

    /** @param String[] $globExclude */
    private static function isEqualAbsolutePath(string $actualFile, GlobMenuEntryNode $parsedMenuEntryNode, string $currentFile, array $globExclude): bool
    {
        $expectedFile = $parsedMenuEntryNode->getUrl();
        if (!self::isAbsoluteFile($expectedFile)) {
            return false;
        }

        if ($expectedFile === '/' . $actualFile) {
            return true;
        }

        return self::isGlob($actualFile, $currentFile, $expectedFile, '/', $globExclude);
    }

    /** @param String[] $globExclude */
    private static function isEqualRelativePath(string $actualFile, GlobMenuEntryNode $menuEntryNode, string $currentFile, array $globExclude): bool
    {
        $expectedFile = $menuEntryNode->getUrl();
        if (self::isAbsoluteFile($expectedFile)) {
            return false;
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

    public static function isAbsoluteFile(string $expectedFile): bool
    {
        return str_starts_with($expectedFile, '/');
    }
}
