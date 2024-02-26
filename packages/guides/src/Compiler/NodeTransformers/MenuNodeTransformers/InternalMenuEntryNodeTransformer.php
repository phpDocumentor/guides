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
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use Psr\Log\LoggerInterface;

use function array_pop;
use function assert;
use function explode;
use function implode;
use function sprintf;

final class InternalMenuEntryNodeTransformer extends AbstractMenuEntryNodeTransformer
{
    use MenuEntryManagement;
    use SubSectionHierarchyHandler;

    // Setting a default level prevents PHP errors in case of circular references
    private const DEFAULT_MAX_LEVELS = 10;

    public function __construct(
        LoggerInterface $logger,
        private readonly DocumentNameResolverInterface $documentNameResolver,
    ) {
        parent::__construct($logger);
    }

    public function supports(Node $node): bool
    {
        return $node instanceof InternalMenuEntryNode;
    }

    /** @return list<MenuEntryNode> */
    protected function handleMenuEntry(MenuNode $currentMenu, MenuEntryNode $entryNode, CompilerContextInterface $compilerContext): array
    {
        assert($entryNode instanceof InternalMenuEntryNode);
        $documentEntries = $compilerContext->getProjectNode()->getAllDocumentEntries();
        $currentPath = $compilerContext->getDocumentNode()->getFilePath();
        $maxDepth = (int) $currentMenu->getOption('maxdepth', self::DEFAULT_MAX_LEVELS);

        foreach ($documentEntries as $documentEntry) {
            if (
                !self::matches($documentEntry->getFile(), $entryNode, $currentPath)
            ) {
                continue;
            }

            $titleNode = $documentEntry->getTitle();
            $navigationTitle =  $documentEntry->getAdditionalData('navigationTitle');
            if ($navigationTitle instanceof TitleNode) {
                $titleNode = $navigationTitle;
            }

            if ($entryNode->getValue() instanceof TitleNode) {
                $titleNode = $entryNode->getValue();
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
                $this->addSubSectionsToMenuEntries($documentEntry, $newEntryNode, $maxDepth);
            }

            if ($currentMenu instanceof TocNode) {
                $this->attachDocumentEntriesToParents($documentEntriesInTree, $compilerContext, $currentPath);
            }

            return [$newEntryNode];
        }

        $this->logger->warning(sprintf('Menu entry "%s" was not found in the document tree. Ignoring it. ', $entryNode->getUrl()), $compilerContext->getLoggerInformation());

        return [];
    }

    private function matches(string $actualFile, InternalMenuEntryNode $parsedMenuEntryNode, string $currentFile): bool
    {
        $expectedFile = $parsedMenuEntryNode->getUrl();
        if (self::isAbsoluteFile($expectedFile)) {
            return $expectedFile === '/' . $actualFile;
        }

        $current = explode('/', $currentFile);
        array_pop($current);


        $absoluteExpectedFile = $this->documentNameResolver->canonicalUrl(implode('/', $current), $expectedFile);

        return $absoluteExpectedFile === $actualFile;
    }

    public function getPriority(): int
    {
        // After DocumentEntryTransformer
        return 4500;
    }
}
