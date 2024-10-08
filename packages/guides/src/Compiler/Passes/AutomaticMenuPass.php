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
use phpDocumentor\Guides\Settings\SettingsManager;

final class AutomaticMenuPass implements CompilerPass
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
        if (!$this->settingsManager->getProjectSettings()->isAutomaticMenu()) {
            return $documents;
        }

        $projectNode = $compilerContext->getProjectNode();
        $rootDocumentEntry = $projectNode->getRootDocumentEntry();
        foreach ($documents as $documentNode) {
            if ($documentNode->isOrphan()) {
                // Do not add orphans to the automatic menu
                continue;
            }

            if ($documentNode->isRoot()) {
                continue;
            }

            $documentEntry = $projectNode->getDocumentEntry($documentNode->getFilePath());
            $documentEntry->setParent($rootDocumentEntry);
            $rootDocumentEntry->addChild($documentEntry);
        }

        return $documents;
    }
}
