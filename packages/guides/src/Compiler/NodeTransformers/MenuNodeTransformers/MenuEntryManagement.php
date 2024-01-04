<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;

use function sprintf;
use function str_starts_with;

trait MenuEntryManagement
{
    /** @param DocumentEntryNode[] $documentEntriesInTree */
    private function attachDocumentEntriesToParents(
        array $documentEntriesInTree,
        CompilerContext $compilerContext,
        string $currentPath,
    ): void {
        foreach ($documentEntriesInTree as $documentEntryInToc) {
            if ($documentEntryInToc->isRoot() || $currentPath === $documentEntryInToc->getFile()) {
                // The root page may not be attached to any other
                continue;
            }

            if ($documentEntryInToc->getParent() !== null && $documentEntryInToc->getParent() !== $compilerContext->getDocumentNode()->getDocumentEntry()) {
                $this->logger->warning(sprintf(
                    'Document %s has been added to parents %s and %s. The `toctree` directive changes the '
                    . 'position of documents in the document tree. Use the `menu` directive to only display a menu without changing the document tree.',
                    $documentEntryInToc->getFile(),
                    $documentEntryInToc->getParent()->getFile(),
                    $compilerContext->getDocumentNode()->getDocumentEntry()->getFile(),
                ), $compilerContext->getLoggerInformation());
            }

            if ($documentEntryInToc->getParent() !== null) {
                continue;
            }

            $documentEntryInToc->setParent($compilerContext->getDocumentNode()->getDocumentEntry());
            $compilerContext->getDocumentNode()->getDocumentEntry()->addChild($documentEntryInToc);
        }
    }

    private function isInRootline(DocumentEntryNode $menuEntry, DocumentEntryNode $currentDoc): bool
    {
        return $menuEntry->getFile() === $currentDoc->getFile()
            || ($currentDoc->getParent() !== null
                && self::isInRootline($menuEntry, $currentDoc->getParent()));
    }

    private function isCurrent(DocumentEntryNode $menuEntry, string $currentPath): bool
    {
        return $menuEntry->getFile() === $currentPath;
    }

    private static function isAbsoluteFile(string $expectedFile): bool
    {
        return str_starts_with($expectedFile, '/');
    }
}
