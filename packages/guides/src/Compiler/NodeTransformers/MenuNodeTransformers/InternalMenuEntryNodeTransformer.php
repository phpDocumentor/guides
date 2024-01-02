<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use Psr\Log\LoggerInterface;

use function array_pop;
use function assert;
use function explode;
use function implode;

class InternalMenuEntryNodeTransformer extends AbstractMenuEntryNodeTransformer
{
    // Setting a default level prevents PHP errors in case of circular references
    private const DEFAULT_MAX_LEVELS = 10;

    public function __construct(
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    public function supports(Node $node): bool
    {
        return $node instanceof MenuNode || $node instanceof InternalMenuEntryNode;
    }

    /** @return list<MenuEntryNode> */
    protected function handleMenuEntry(MenuNode $currentMenu, MenuEntryNode $node, CompilerContext $compilerContext): array
    {
        assert($node instanceof InternalMenuEntryNode);
        $documentEntries = $compilerContext->getProjectNode()->getAllDocumentEntries();
        $currentPath = $compilerContext->getDocumentNode()->getFilePath();
        $maxDepth = (int) $currentMenu->getOption('maxdepth', self::DEFAULT_MAX_LEVELS);
        foreach ($documentEntries as $documentEntry) {
            if (
                !self::isEqualAbsolutePath($documentEntry->getFile(), $node, $currentPath)
                && !self::isEqualRelativePath($documentEntry->getFile(), $node, $currentPath)
            ) {
                continue;
            }

            $documentEntriesInTree[] = $documentEntry;
            $menuEntry = new InternalMenuEntryNode(
                $documentEntry->getFile(),
                $node->getValue() ?? $documentEntry->getTitle(),
                [],
                false,
                1,
                '',
                $this->isInRootline($documentEntry, $compilerContext->getDocumentNode()->getDocumentEntry()),
                $this->isCurrent($documentEntry, $currentPath),
            );
            if (!$currentMenu->hasOption('titlesonly')) {
                $this->addSubSectionsToMenuEntries($documentEntry, $menuEntry, $maxDepth - 1);
            }

            if ($currentMenu instanceof TocNode) {
                $this->attachDocumentEntriesToParents($documentEntriesInTree, $compilerContext, $currentPath);
            }

            return [$menuEntry];
        }

        return [$node];
    }

    private static function isEqualAbsolutePath(string $actualFile, InternalMenuEntryNode $parsedMenuEntryNode, string $currentFile): bool
    {
        $expectedFile = $parsedMenuEntryNode->getUrl();
        if (!self::isAbsoluteFile($expectedFile)) {
            return false;
        }

        return $expectedFile === '/' . $actualFile;
    }

    private static function isEqualRelativePath(string $actualFile, InternalMenuEntryNode $menuEntryNode, string $currentFile): bool
    {
        $expectedFile = $menuEntryNode->getUrl();
        if (self::isAbsoluteFile($expectedFile)) {
            return false;
        }

        $current = explode('/', $currentFile);
        array_pop($current);
        $current[] = $expectedFile;
        $absoluteExpectedFile = implode('/', $current);

        return $absoluteExpectedFile === $actualFile;
    }

    public function getPriority(): int
    {
        // After DocumentEntryTransformer
        return 4500;
    }
}
