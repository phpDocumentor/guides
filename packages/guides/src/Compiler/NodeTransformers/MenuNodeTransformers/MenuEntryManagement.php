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
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\ExternalEntryNode;

use function sprintf;
use function str_starts_with;

trait MenuEntryManagement
{
    /** @param array<DocumentEntryNode|ExternalEntryNode> $entryNodes */
    private function attachDocumentEntriesToParents(
        array $entryNodes,
        CompilerContextInterface $compilerContext,
        string $currentPath,
    ): void {
        foreach ($entryNodes as $entryNode) {
            if ($entryNode instanceof DocumentEntryNode) {
                if (($entryNode->isRoot() || $currentPath === $entryNode->getFile())) {
                    // The root page may not be attached to any other
                    continue;
                }

                if ($entryNode->getParent() !== null && $entryNode->getParent() !== $compilerContext->getDocumentNode()->getDocumentEntry()) {
                    $this->logger->warning(sprintf(
                        'Document %s has been added to parents %s and %s. The `toctree` directive changes the '
                        . 'position of documents in the document tree. Use the `menu` directive to only display a menu without changing the document tree.',
                        $entryNode->getFile(),
                        $entryNode->getParent()->getFile(),
                        $compilerContext->getDocumentNode()->getDocumentEntry()->getFile(),
                    ), $compilerContext->getLoggerInformation());
                }

                if ($entryNode->getParent() !== null) {
                    continue;
                }
            }

            $entryNode->setParent($compilerContext->getDocumentNode()->getDocumentEntry());
            $compilerContext->getDocumentNode()->getDocumentEntry()->addChild($entryNode);
        }
    }

    private static function isInRootline(DocumentEntryNode $menuEntry, DocumentEntryNode $currentDoc): bool
    {
        return $menuEntry->getFile() === $currentDoc->getFile()
            || ($currentDoc->getParent() !== null
                && self::isInRootline($menuEntry, $currentDoc->getParent()));
    }

    private static function isCurrent(DocumentEntryNode $menuEntry, string $currentPath): bool
    {
        return $menuEntry->getFile() === $currentPath;
    }

    private static function isAbsoluteFile(string $expectedFile): bool
    {
        return str_starts_with($expectedFile, '/');
    }
}
