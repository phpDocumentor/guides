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
use phpDocumentor\Guides\Exception\DocumentEntryNotFound;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\ExternalEntryNode;
use phpDocumentor\Guides\Nodes\Menu\ExternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;

use function assert;
use function sprintf;

final class SubInternalMenuEntryNodeTransformer extends AbstractMenuEntryNodeTransformer
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
    protected function handleMenuEntry(MenuNode $currentMenu, MenuEntryNode $entryNode, CompilerContextInterface $compilerContext): array
    {
        assert($entryNode instanceof InternalMenuEntryNode);
        $maxDepth = (int) $currentMenu->getOption('maxdepth', self::DEFAULT_MAX_LEVELS);
        try {
            $documentEntryOfMenuEntry = $compilerContext->getProjectNode()->getDocumentEntry($entryNode->getUrl());
        } catch (DocumentEntryNotFound) {
            $this->logger->warning(sprintf('Menu entry "%s" was not found in the document tree. Ignoring it. ', $entryNode->getUrl()), $compilerContext->getLoggerInformation());

            return [];
        }

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
        CompilerContextInterface $compilerContext,
        InternalMenuEntryNode $sectionMenuEntry,
        DocumentEntryNode $documentEntry,
        int $currentLevel,
        int $maxDepth,
    ): void {
        if ($maxDepth < $currentLevel) {
            return;
        }

        foreach ($documentEntry->getMenuEntries() as $subEntryNode) {
            if ($subEntryNode instanceof DocumentEntryNode) {
                $titleNode = $subEntryNode->getTitle();
                $navigationTitle =  $subEntryNode->getAdditionalData('navigationTitle');
                if ($navigationTitle instanceof TitleNode) {
                    $titleNode = $navigationTitle;
                }

                $subMenuEntry = new InternalMenuEntryNode(
                    $subEntryNode->getFile(),
                    $titleNode,
                    [],
                    false,
                    $currentLevel,
                    '',
                    self::isInRootline($subEntryNode, $compilerContext->getDocumentNode()->getDocumentEntry()),
                    self::isCurrent($subEntryNode, $compilerContext->getDocumentNode()->getFilePath()),
                );

                if (!$currentMenu->hasOption('titlesonly') && $maxDepth - $currentLevel + 1 > 1) {
                    $this->addSubSectionsToMenuEntries($subEntryNode, $subMenuEntry, $maxDepth - $currentLevel + 2);
                }

                $sectionMenuEntry->addMenuEntry($subMenuEntry);
                $this->addSubEntries($currentMenu, $compilerContext, $subMenuEntry, $subEntryNode, $currentLevel + 1, $maxDepth);
                continue;
            }

            if (!($subEntryNode instanceof ExternalEntryNode)) {
                continue;
            }

            $subMenuEntry = new ExternalMenuEntryNode(
                $subEntryNode->getValue(),
                TitleNode::fromString($subEntryNode->getTitle()),
                $currentLevel,
            );
            $sectionMenuEntry->addMenuEntry($subMenuEntry);
        }
    }
}
