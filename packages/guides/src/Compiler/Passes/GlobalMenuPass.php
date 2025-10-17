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

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\EntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\ExternalEntryNode;
use phpDocumentor\Guides\Nodes\Menu\ExternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\NavMenuNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Settings\SettingsManager;
use Throwable;

use function array_map;
use function assert;
use function count;

use const PHP_INT_MAX;

final class GlobalMenuPass implements CompilerPass
{
    public function __construct(
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function getPriority(): int
    {
        return 20; // must be run very late
    }

    /**
     * @param DocumentNode[] $documents
     *
     * @return DocumentNode[]
     */
    public function run(array $documents, CompilerContextInterface $compilerContext): array
    {
        $projectNode = $compilerContext->getProjectNode();
        try {
            $rootDocumentEntry = $projectNode->getRootDocumentEntry();
        } catch (Throwable) {
            // Todo: Functional tests have no root document entry
            return $documents;
        }

        $rootDocument = null;
        foreach ($documents as $document) {
            if ($document->getDocumentEntry() === $rootDocumentEntry) {
                $rootDocument = $document;
                break;
            }
        }

        if ($rootDocument === null) {
            return $documents;
        }

        $menuNodes = [];
        foreach ($rootDocument->getTocNodes() as $tocNode) {
            $menuNode = $this->getNavMenuNodefromTocNode($compilerContext, $tocNode);
            $menuNodes[] = $menuNode->withCaption($tocNode->getCaption());
        }

        if ($this->settingsManager->getProjectSettings()->isAutomaticMenu() && count($menuNodes) === 0) {
            $menuNodes[] = $this->getNavMenuNodeFromDocumentEntries($compilerContext);
        }

        $projectNode->setGlobalMenues($menuNodes);

        return $documents;
    }

    private function getNavMenuNodeFromDocumentEntries(CompilerContextInterface $compilerContext): NavMenuNode
    {
        $rootDocumentEntry = $compilerContext->getProjectNode()->getRootDocumentEntry();
        $menuEntries = $this->getMenuEntriesFromDocumentEntries($rootDocumentEntry);

        return new NavMenuNode($menuEntries);
    }

    /** @return InternalMenuEntryNode[] */
    public function getMenuEntriesFromDocumentEntries(DocumentEntryNode $rootDocumentEntry): array
    {
        $menuEntries = [];
        foreach ($rootDocumentEntry->getChildren() as $documentEntryNode) {
            $children = $this->getMenuEntriesFromDocumentEntries($documentEntryNode);
            $newMenuEntry = new InternalMenuEntryNode($documentEntryNode->getFile(), $documentEntryNode->getTitle(), $children, false, 1);
            $menuEntries[] = $newMenuEntry;
        }

        return $menuEntries;
    }

    private function getNavMenuNodefromTocNode(CompilerContextInterface $compilerContext, TocNode $tocNode, string|null $menuType = null): NavMenuNode
    {
        $self = $this;
        $menuEntries = array_map(static function (MenuEntryNode $tocEntry) use ($compilerContext, $self) {
            return $self->getMenuEntryWithChildren($compilerContext, $tocEntry);
        }, $tocNode->getMenuEntries());
        $node = new NavMenuNode($menuEntries);
        $options = $tocNode->getOptions();
        unset($options['hidden']);
        unset($options['titlesonly']);
        unset($options['maxdepth']);
        if ($menuType !== null) {
            $options['menu'] = $menuType;
        }

        $node = $node->withOptions($options);
        assert($node instanceof NavMenuNode);

        return $node;
    }

    private function getMenuEntryWithChildren(CompilerContextInterface $compilerContext, MenuEntryNode $menuEntry): MenuEntryNode
    {
        if (!$menuEntry instanceof InternalMenuEntryNode) {
            return $menuEntry;
        }

        $newMenuEntry = new InternalMenuEntryNode($menuEntry->getUrl(), $menuEntry->getValue(), [], false, 1);
        $maxdepth = $this->settingsManager->getProjectSettings()->getMaxMenuDepth();
        $maxdepth = $maxdepth < 1 ? PHP_INT_MAX : $maxdepth + 1;
        $documentEntryOfMenuEntry = $compilerContext->getProjectNode()->getDocumentEntry($menuEntry->getUrl());
        $this->addSubEntries($compilerContext, $newMenuEntry, $documentEntryOfMenuEntry, 2, $maxdepth);

        return $newMenuEntry;
    }

    /** @param EntryNode<DocumentEntryNode|ExternalEntryNode>|ExternalEntryNode $entryNode */
    private function addSubEntries(
        CompilerContextInterface $compilerContext,
        MenuEntryNode $sectionMenuEntry,
        EntryNode $entryNode,
        int $currentLevel,
        int $maxDepth,
    ): void {
        if ($maxDepth <= $currentLevel) {
            return;
        }

        if (!$sectionMenuEntry instanceof InternalMenuEntryNode) {
            return;
        }

        if (!$entryNode instanceof DocumentEntryNode) {
            return;
        }

        foreach ($entryNode->getMenuEntries() as $subEntryNode) {
            $subMenuEntry = match ($subEntryNode::class) {
                DocumentEntryNode::class => $this->createInternalMenuEntry($subEntryNode, $currentLevel),
                ExternalEntryNode::class => $this->createExternalMenuEntry($subEntryNode, $currentLevel),
            };

            $sectionMenuEntry->addMenuEntry($subMenuEntry);
            $this->addSubEntries(
                $compilerContext,
                $subMenuEntry,
                $subEntryNode,
                $currentLevel + 1,
                $maxDepth,
            );
        }
    }

    private function createInternalMenuEntry(DocumentEntryNode $subEntryNode, int $currentLevel): InternalMenuEntryNode
    {
        $titleNode = $subEntryNode->getTitle();
        $navigationTitle =  $subEntryNode->getAdditionalData('navigationTitle');
        if ($navigationTitle instanceof TitleNode) {
            $titleNode = $navigationTitle;
        }

        return new InternalMenuEntryNode(
            $subEntryNode->getFile(),
            $titleNode,
            [],
            false,
            $currentLevel,
            '',
        );
    }

    private function createExternalMenuEntry(ExternalEntryNode $subEntryNode, int $currentLevel): ExternalMenuEntryNode
    {
        return new ExternalMenuEntryNode(
            $subEntryNode->getValue(),
            TitleNode::fromString($subEntryNode->getTitle()),
            $currentLevel,
        );
    }
}
