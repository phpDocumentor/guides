<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Menu\NavMenuNode;
use Throwable;

class GlobalMenuPass implements CompilerPass
{
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
            return [];
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
            $menuNode = NavMenuNode::fromTocNode($tocNode);
            $menuNodes[] = $menuNode->withCaption($tocNode->getCaption());
        }

        $projectNode->setGlobalMenues($menuNodes);

        return $documents;
    }
}
