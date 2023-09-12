<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\SectionEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\NavMenuNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use Psr\Log\LoggerInterface;

use function array_pop;
use function array_unique;
use function assert;
use function explode;
use function implode;
use function in_array;
use function preg_match;
use function sprintf;
use function str_replace;
use function str_starts_with;

/** @implements NodeTransformer<MenuNode> */
class MenuNodeAddEntryTransformer implements NodeTransformer
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        if (!$node instanceof TocNode && !$node instanceof NavMenuNode) {
            return $node;
        }

        $files = $node->getFiles();
        $glob = $node->hasOption('glob');
        $globExclude = explode(',', $node->getOption('globExclude') . '');

        $documentEntries = $compilerContext->getProjectNode()->getAllDocumentEntries();
        $currentPath = $compilerContext->getDocumentNode()->getFilePath();
        $documentEntriesInTree = [];
        $menuEntries = [];

        foreach ($files as $file) {
            foreach ($documentEntries as $documentEntry) {
                if (
                    !self::isEqualAbsolutePath($documentEntry->getFile(), $file, $currentPath, $glob, $globExclude)
                    && !self::isEqualRelativePath($documentEntry->getFile(), $file, $currentPath, $glob, $globExclude)
                ) {
                    continue;
                }

                if ($node instanceof TocNode && $glob && self::isCurrent($documentEntry, $currentPath)) {
                    // TocNodes do not select the current page in glob mode. In a menu we might want to display it
                    continue;
                }

                $documentEntriesInTree[] = $documentEntry;
                $menuEntry = new MenuEntryNode(
                    $documentEntry->getFile(),
                    $documentEntry->getTitle(),
                    [],
                    false,
                    1,
                    '',
                    $this->isInRootline($documentEntry, $compilerContext->getDocumentNode()->getDocumentEntry()),
                    $this->isCurrent($documentEntry, $currentPath),
                );
                if (!$node->hasOption('titlesonly')) {
                    $this->addSubSectionsToMenuEntries($documentEntry, $menuEntry);
                }

                if ($node instanceof TocNode) {
                    $this->attachDocumentEntriesToParents($documentEntriesInTree, $compilerContext, $currentPath);
                }

                $menuEntries[] = $menuEntry;
                if (!$glob) {
                    // If glob is not set, there may only be one result per listed file
                    break;
                }
            }
        }

        $menuEntries = array_unique($menuEntries);
        $node = $node->withMenuEntries($menuEntries);
        if ($node instanceof TocNode) {
            $compilerContext->getDocumentNode()->addTocNode($node);
        }

        return $node;
    }

    private function isInRootline(DocumentEntryNode $menuEntry, DocumentEntryNode $currentDoc): bool
    {
        return $menuEntry->getFile() === $currentDoc->getFile()
            || ($currentDoc->getParent() !== null
                && self::isInRootline($menuEntry, $currentDoc->getParent()));
    }

    private function isCurrent(DocumentEntryNode $menuEntry, string $currentPath): bool
    {
        return $menuEntry->getFile() === $currentPath;
    }

    private function addSubSections(MenuEntryNode $sectionMenuEntry, SectionEntryNode $sectionEntryNode, DocumentEntryNode $documentEntry, int $currentLevel): void
    {
        foreach ($sectionEntryNode->getChildren() as $subSectionEntryNode) {
            $subSectionMenuEntry = new MenuEntryNode($documentEntry->getFile(), $subSectionEntryNode->getTitle(), [], false, $currentLevel + 1, $subSectionEntryNode->getId());
            $sectionMenuEntry->addSection($subSectionMenuEntry);
        }
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TocNode || $node instanceof NavMenuNode;
    }

    public function getPriority(): int
    {
        // After DocumentEntryTransformer
        return 4500;
    }

    /** @param String[] $globExclude */
    private static function isEqualAbsolutePath(string $actualFile, string $expectedFile, string $currentFile, bool $glob, array $globExclude): bool
    {
        if (!self::isAbsoluteFile($expectedFile)) {
            return false;
        }

        if ($expectedFile === '/' . $actualFile) {
            return true;
        }

        return self::isGlob($glob, $actualFile, $currentFile, $expectedFile, '/', $globExclude);
    }

    /** @param String[] $globExclude */
    private static function isEqualRelativePath(string $actualFile, string $expectedFile, string $currentFile, bool $glob, array $globExclude): bool
    {
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

        return self::isGlob($glob, $actualFile, $currentFile, $absoluteExpectedFile, '', $globExclude);
    }

    /** @param String[] $globExclude */
    private static function isGlob(bool $glob, string $documentEntryFile, string $currentPath, string $file, string $prefix, array $globExclude): bool
    {
        if ($glob && !in_array($documentEntryFile, $globExclude, true)) {
            $file = str_replace('*', '[^\/]*', $file);
            $pattern = '`^' . $file . '$`';

            return preg_match($pattern, $prefix . $documentEntryFile) > 0;
        }

        return false;
    }

    public static function isPatternMatchingFile(string $absoluteExpectedFile, string $actualFile): bool
    {
        $pattern = str_replace('*', '[a-zA-Z0-9-_]*', $absoluteExpectedFile);
        $pattern = '`^' . $pattern . '$`';

        return preg_match($pattern, $actualFile) > 0;
    }

    public static function isAbsoluteFile(string $expectedFile): bool
    {
        return str_starts_with($expectedFile, '/');
    }

    /** @param DocumentEntryNode[] $documentEntriesInTree */
    private function attachDocumentEntriesToParents(
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

    private function addSubSectionsToMenuEntries(DocumentEntryNode $documentEntry, MenuEntryNode $menuEntry): void
    {
        foreach ($documentEntry->getSections() as $section) {
            // We do not add the main section as it repeats the document title
            foreach ($section->getChildren() as $subSectionEntryNode) {
                assert($subSectionEntryNode instanceof SectionEntryNode);
                $currentLevel = $menuEntry->getLevel() + 1;
                $sectionMenuEntry = new MenuEntryNode(
                    $documentEntry->getFile(),
                    $subSectionEntryNode->getTitle(),
                    [],
                    false,
                    $currentLevel,
                    $subSectionEntryNode->getId(),
                );
                $menuEntry->addSection($sectionMenuEntry);
                $this->addSubSections($sectionMenuEntry, $subSectionEntryNode, $documentEntry, $currentLevel);
            }
        }
    }
}
