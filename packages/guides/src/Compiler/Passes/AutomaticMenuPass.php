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
use Psr\Log\LoggerInterface;

use function array_pop;
use function count;
use function explode;
use function implode;
use function in_array;
use function sprintf;

final class AutomaticMenuPass implements CompilerPass
{
    public function __construct(
        private readonly SettingsManager $settingsManager,
        private readonly LoggerInterface|null $logger = null,
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
        $indexNames = explode(',', $this->settingsManager->getProjectSettings()->getIndexName());
        foreach ($documents as $documentNode) {
            if ($documentNode->isOrphan()) {
                // Do not add orphans to the automatic menu
                continue;
            }

            if ($documentNode->isRoot()) {
                continue;
            }

            $filePath = $documentNode->getFilePath();
            $pathParts = explode('/', $filePath);
            $documentEntry = $projectNode->getDocumentEntry($filePath);
            if (count($pathParts) === 1 || count($pathParts) === 2 && in_array($pathParts[1], $indexNames, true)) {
                $documentEntry->setParent($rootDocumentEntry);
                $rootDocumentEntry->addChild($documentEntry);
                continue;
            }

            $fileName = array_pop($pathParts);
            $path = implode('/', $pathParts);
            if (in_array($fileName, $indexNames, true)) {
                array_pop($pathParts);
                $path = implode('/', $pathParts);
            }

            $parentFound = false;
            foreach ($indexNames as $indexName) {
                $indexFile = $path . '/' . $indexName;
                $parentEntry = $projectNode->findDocumentEntry($indexFile);
                if ($parentEntry === null) {
                    continue;
                }

                $documentEntry->setParent($parentEntry);
                $parentEntry->addChild($documentEntry);
                $parentFound = true;
                break;
            }

            if ($parentFound) {
                continue;
            }

            $parentEntry = $projectNode->findDocumentEntry($path);
            if ($parentEntry === null) {
                $this->logger?->warning(sprintf('No parent found for file "%s/%s" attaching it to the document root instead. ', $path, $fileName));
                continue;
            }

            $documentEntry->setParent($parentEntry);
            $parentEntry->addChild($documentEntry);
        }

        return $documents;
    }
}
