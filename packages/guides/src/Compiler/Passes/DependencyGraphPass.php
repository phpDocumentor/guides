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

use phpDocumentor\Guides\Build\IncrementalBuild\IncrementalBuildState;
use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Inline\CrossReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ProjectNode;

use function array_unique;
use function strpos;
use function substr;

/**
 * Builds the dependency graph by finding cross-references between documents.
 *
 * Tracks which documents import from which others, enabling proper
 * dirty propagation during incremental rendering.
 *
 * Runs after ExportsCollectorPass, after all menu generation.
 *
 * Completeness Check:
 * After calling run(), callers should check hasRejectedImports() to detect
 * if any imports were rejected due to graph size limits. If true, the dependency
 * graph is incomplete and incremental builds may miss some dependencies.
 * In this case, a full rebuild is recommended.
 *
 * @see DependencyGraph::MAX_DOCUMENTS
 * @see DependencyGraph::MAX_IMPORTS_PER_DOCUMENT
 */
final class DependencyGraphPass implements CompilerPass
{
    use NodeTraversalTrait;

    /** Priority: runs after ExportsCollectorPass (10) */
    private const PRIORITY = 9;

    /** Count of imports that were rejected due to limits */
    private int $rejectedImports = 0;

    public function __construct(
        private readonly IncrementalBuildState $buildState,
    ) {
    }

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    /**
     * @param DocumentNode[] $documents
     *
     * @return DocumentNode[]
     */
    public function run(array $documents, CompilerContextInterface $compilerContext): array
    {
        $projectNode = $compilerContext->getProjectNode();
        $graph = $this->buildState->getDependencyGraph();
        $this->rejectedImports = 0;

        foreach ($documents as $document) {
            $filePath = $document->getFilePath();

            // Clear old imports for this document
            $graph->clearImportsFor($filePath);

            // Find all references in this document
            $imports = $this->findImports($document, $projectNode);

            // Add edges to the graph, tracking rejections
            foreach ($imports as $importedDocPath) {
                if ($graph->addImport($filePath, $importedDocPath)) {
                    continue;
                }

                $this->rejectedImports++;
            }
        }

        return $documents;
    }

    /**
     * Get the count of imports that were rejected due to limits.
     *
     * If this is non-zero, the dependency graph may be incomplete and
     * incremental builds may miss some dependencies.
     */
    public function getRejectedImportCount(): int
    {
        return $this->rejectedImports;
    }

    /**
     * Check if any imports were rejected due to limits.
     *
     * If true, the dependency graph is incomplete and incremental builds
     * may miss some dependencies. A full rebuild is recommended.
     */
    public function hasRejectedImports(): bool
    {
        return $this->rejectedImports > 0;
    }

    /**
     * Find all documents that this document imports from.
     *
     * @return string[] Imported document paths
     */
    private function findImports(DocumentNode $document, ProjectNode $projectNode): array
    {
        $imports = [];
        $filePath = $document->getFilePath();

        $this->traverseNodes($document->getChildren(), function (Node $node) use (&$imports, $projectNode, $filePath): void {
            // Handle :doc:`reference`
            if ($node instanceof DocReferenceNode) {
                $targetDoc = $this->resolveDocReference($node, $projectNode);
                if ($targetDoc !== null && $targetDoc !== $filePath) {
                    $imports[] = $targetDoc;
                }

                return;
            }

            // Handle :ref:`reference`
            if ($node instanceof ReferenceNode) {
                $targetDoc = $this->resolveRefReference($node, $projectNode);
                if ($targetDoc !== null && $targetDoc !== $filePath) {
                    $imports[] = $targetDoc;
                }

                return;
            }

            // Handle any other CrossReferenceNode
            if (!($node instanceof CrossReferenceNode)) {
                return;
            }

            $targetDoc = $this->resolveCrossReference($node, $projectNode);
            if ($targetDoc === null || $targetDoc === $filePath) {
                return;
            }

            $imports[] = $targetDoc;
        });

        return array_unique($imports);
    }

    /**
     * Resolve a :doc: reference to its target document.
     *
     * Note: When the target document is not found in the project, the raw target
     * path is returned. This is intentional as it tracks intended dependencies
     * even for unresolved references (e.g., references to documents being added
     * in the same build). The rendering phase handles error reporting for
     * actually missing documents.
     */
    private function resolveDocReference(DocReferenceNode $node, ProjectNode $projectNode): string|null
    {
        // Skip interlink references to external projects
        if ($node->getInterlinkDomain() !== '') {
            return null;
        }

        // The target is the document path
        $target = $node->getTargetReference();

        // Strip any anchor
        $hashPos = strpos($target, '#');
        if ($hashPos !== false) {
            $target = substr($target, 0, $hashPos);
        }

        // Check if document exists in project
        $entry = $projectNode->findDocumentEntry($target);
        if ($entry !== null) {
            return $entry->getFile();
        }

        // Return raw target for unresolved references (see method doc)
        return $target !== '' ? $target : null;
    }

    /**
     * Resolve a :ref: reference to its target document.
     */
    private function resolveRefReference(ReferenceNode $node, ProjectNode $projectNode): string|null
    {
        // Skip interlink references to external projects
        if ($node->getInterlinkDomain() !== '') {
            return null;
        }

        $targetAnchor = $node->getTargetReference();
        $linkType = $node->getLinkType();

        // Look up the anchor in the project's internal targets
        $target = $projectNode->getInternalTarget($targetAnchor, $linkType);
        if ($target !== null) {
            return $target->getDocumentPath();
        }

        // Try default link type
        $target = $projectNode->getInternalTarget($targetAnchor);

        return $target?->getDocumentPath();
    }

    /**
     * Resolve any CrossReferenceNode to its target document.
     */
    private function resolveCrossReference(CrossReferenceNode $node, ProjectNode $projectNode): string|null
    {
        // Skip interlink references to external projects
        if ($node->getInterlinkDomain() !== '') {
            return null;
        }

        // Try to resolve using the reference target
        $targetAnchor = $node->getTargetReference();

        $target = $projectNode->getInternalTarget($targetAnchor);

        return $target?->getDocumentPath();
    }
}
