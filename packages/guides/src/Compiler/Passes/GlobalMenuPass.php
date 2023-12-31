<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\NavMenuNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Settings\SettingsManager;
use Throwable;

use function array_map;
use function assert;

use const PHP_INT_MAX;

class GlobalMenuPass implements CompilerPass
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
    public function run(array $documents, CompilerContext $compilerContext): array
    {
        $projectNode = $compilerContext->getProjectNode();
        try {
            $rootDocumentEntry = $projectNode->getRootDocumentEntry();
        } catch (Throwable) {
            // Todo: Functional tests have not root document entry
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
            return [];
        }

        $menuNodes = [];
        foreach ($rootDocument->getTocNodes() as $tocNode) {
            $menuNode = $this->getNavMenuNodefromTocNode($compilerContext, $tocNode);
            $menuNodes[] = $menuNode->withCaption($tocNode->getCaption());
        }

        $projectNode->setGlobalMenues($menuNodes);

        return $documents;
    }

    private function getNavMenuNodefromTocNode(CompilerContext $compilerContext, TocNode $tocNode, string|null $menuType = null): NavMenuNode
    {
        $node = new NavMenuNode($tocNode->getParsedMenuEntryNodes());
        $self = $this;
        $menuEntries = array_map(static function (MenuEntryNode $tocEntry) use ($compilerContext, $self) {
            return $self->getMenuEntryWithChildren($compilerContext, $tocEntry);
        }, $tocNode->getMenuEntries());
        $node = $node->withMenuEntries($menuEntries);
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

    private function getMenuEntryWithChildren(CompilerContext $compilerContext, MenuEntryNode $menuEntry): MenuEntryNode
    {
        if (!$menuEntry instanceof InternalMenuEntryNode) {
            return $menuEntry;
        }

        $newMenuEntry = new InternalMenuEntryNode($menuEntry->getUrl(), $menuEntry->getValue(), [], false, 2);
        $maxdepth = $this->settingsManager->getProjectSettings()->getMaxMenuDepth();
        $maxdepth = $maxdepth < 1 ? PHP_INT_MAX : $maxdepth + 1;
        $documentEntryOfMenuEntry = $compilerContext->getProjectNode()->getDocumentEntry($menuEntry->getUrl());
        $this->addSubEntries($compilerContext, $newMenuEntry, $documentEntryOfMenuEntry, 3, $maxdepth);

        return $newMenuEntry;
    }

    private function addSubEntries(
        CompilerContext $compilerContext,
        MenuEntryNode $sectionMenuEntry,
        DocumentEntryNode $documentEntry,
        int $currentLevel,
        int $maxDepth,
    ): void {
        if ($maxDepth < $currentLevel) {
            return;
        }

        if (!$sectionMenuEntry instanceof InternalMenuEntryNode) {
            return;
        }

        foreach ($documentEntry->getChildren() as $subDocumentEntryNode) {
            $subMenuEntry = new InternalMenuEntryNode(
                $subDocumentEntryNode->getFile(),
                $subDocumentEntryNode->getTitle(),
                [],
                false,
                $currentLevel,
                '',
            );
            $sectionMenuEntry->addMenuEntry($subMenuEntry);
            $this->addSubEntries($compilerContext, $subMenuEntry, $subDocumentEntryNode, $currentLevel + 1, $maxDepth);
        }
    }
}
