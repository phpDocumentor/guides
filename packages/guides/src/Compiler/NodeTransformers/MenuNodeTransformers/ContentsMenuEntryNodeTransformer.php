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
use phpDocumentor\Guides\Nodes\DocumentTree\SectionEntryNode;
use phpDocumentor\Guides\Nodes\Menu\ContentMenuNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\SectionMenuEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;

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
    protected function handleMenuEntry(MenuNode $currentMenu, MenuEntryNode $entryNode, CompilerContextInterface $compilerContext): array
    {
        if (!$currentMenu instanceof ContentMenuNode) {
            return [$entryNode];
        }

        assert($entryNode instanceof SectionMenuEntryNode);
        $depth = (int) $currentMenu->getOption('depth', self::DEFAULT_MAX_LEVELS - 1) + 1;
        $documentEntry = $compilerContext->getDocumentNode()->getDocumentEntry();
        if ($currentMenu->isLocal()) {
            $sectionNode = $compilerContext->getShadowTree()->getParent()?->getParent()?->getNode();
            if (!$sectionNode instanceof SectionNode) {
                $this->logger->error('Section of contents directive not found. ', $compilerContext->getLoggerInformation());

                return [];
            }

            $sectionEntry = $documentEntry->findSectionEntry($sectionNode);
            if (!$sectionEntry instanceof SectionEntryNode) {
                $this->logger->error('Section of contents directive not found. ', $compilerContext->getLoggerInformation());

                return [];
            }

            $newEntryNode = new SectionMenuEntryNode(
                $documentEntry->getFile(),
                $entryNode->getValue() ?? $sectionEntry->getTitle(),
                1,
                $sectionEntry->getId(),
            );
            $this->addSubSections($newEntryNode, $sectionEntry, $documentEntry, 1, $depth);
        } else {
            $newEntryNode = new SectionMenuEntryNode(
                $documentEntry->getFile(),
                $entryNode->getValue() ?? $documentEntry->getTitle(),
                1,
            );
            $this->addSubSectionsToMenuEntries($documentEntry, $newEntryNode, $depth);
        }

        return $newEntryNode->getSections();
    }

    public function getPriority(): int
    {
        // After DocumentEntryTransformer
        return 4500;
    }
}
