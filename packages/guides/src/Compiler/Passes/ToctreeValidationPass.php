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
use phpDocumentor\Guides\Nodes\ProjectNode;
use Psr\Log\LoggerInterface;

final class ToctreeValidationPass implements CompilerPass
{
    public function __construct(
        private readonly LoggerInterface $logger,
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

        foreach ($documents as $document) {
            if (!$this->isMissingInToctree($projectNode->getDocumentEntry($document->getFilePath()), $projectNode) || $document->isOrphan()) {
                continue;
            }

            $this->logger->warning(
                'Document "' . $document->getFilePath() . '" isn\'t included in any toctree. Include it in a `.. toctree::` directive or add `:orphan:` in the first line of the rst document',
                $document->getLoggerInformation(),
            );
        }

        return $documents;
    }

    public function isMissingInToctree(DocumentEntryNode $documentEntry, ProjectNode $projectNode): bool
    {
        return $documentEntry->getParent() === null && $documentEntry !== $projectNode->getRootDocumentEntry();
    }
}
