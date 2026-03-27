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

use phpDocumentor\Guides\Build\IncrementalBuild\ContentHasher;
use phpDocumentor\Guides\Build\IncrementalBuild\DocumentExports;
use phpDocumentor\Guides\Build\IncrementalBuild\IncrementalBuildState;
use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Nodes\CitationNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;

use function array_values;
use function file_exists;
use function filemtime;
use function realpath;
use function rtrim;
use function serialize;
use function str_starts_with;
use function time;

/**
 * Collects exports (anchors, titles, citations) from each document
 * for incremental rendering dependency tracking.
 *
 * Runs after all other compilation passes.
 */
final class ExportsCollectorPass implements CompilerPass
{
    use NodeTraversalTrait;

    /** Priority: runs after all other compilation passes */
    private const PRIORITY = 10;

    public function __construct(
        private readonly IncrementalBuildState $buildState,
        private readonly ContentHasher $hasher,
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
        $inputDir = $this->buildState->getInputDir();

        foreach ($documents as $document) {
            $docPath = $document->getFilePath();

            // Collect anchors from this document
            $anchors = $this->collectAnchors($document, $projectNode);

            // Collect section titles
            $sectionTitles = $this->collectSectionTitles($document);

            // Collect citations (if any)
            $citations = $this->collectCitations($document);

            // Compute content hash and mtime from the actual source file
            // Logic flow:
            // 1. If source file found and readable: use file hash + file mtime
            // 2. If filemtime fails (permissions, etc.): use file hash + 0 mtime
            // 3. If no source file or path traversal blocked: use document serialize hash + current time
            $contentHash = '';
            $lastModified = 0;

            if ($inputDir !== '') {
                $sourceFilePath = $this->findSourceFile($inputDir, $docPath);
                if ($sourceFilePath !== null) {
                    // hashFile() already handles TOCTOU race (returns '' if file vanishes)
                    $contentHash = $this->hasher->hashFile($sourceFilePath);
                    // Suppress warning if file vanishes between hashFile and filemtime (TOCTOU)
                    $mtime = @filemtime($sourceFilePath);
                    $lastModified = $mtime !== false ? $mtime : 0;
                }
            }

            // Fallback: hash the document structure when source file not available
            // This happens when: no inputDir set, file not found, or path traversal blocked
            if ($contentHash === '') {
                $contentHash = $this->hasher->hashContent(serialize($document));
                $lastModified = time();
            }

            // Get document title (first heading, used by :doc: references)
            $documentTitle = $document->getTitle()?->toString() ?? '';

            $exportsHash = $this->hasher->hashExports($anchors, $sectionTitles, $citations, $documentTitle);

            $exports = new DocumentExports(
                documentPath: $docPath,
                contentHash: $contentHash,
                exportsHash: $exportsHash,
                anchors: $anchors,
                sectionTitles: $sectionTitles,
                citations: $citations,
                lastModified: $lastModified,
                documentTitle: $documentTitle,
            );

            $this->buildState->setExports($docPath, $exports);
        }

        return $documents;
    }

    /**
     * Find the source file for a document path.
     *
     * Includes path traversal protection to ensure the resolved path
     * stays within the input directory.
     */
    private function findSourceFile(string $inputDir, string $docPath): string|null
    {
        $inputDir = rtrim($inputDir, '/');

        // Resolve the real path of the input directory for comparison
        $realInputDir = realpath($inputDir);
        if ($realInputDir === false) {
            return null;
        }

        // Try common extensions
        foreach (['.rst', '.md', '.txt', ''] as $ext) {
            $tryPath = $inputDir . '/' . $docPath . $ext;
            if (!file_exists($tryPath)) {
                continue;
            }

            // Resolve the real path and verify it's within the input directory
            // This prevents path traversal attacks via "../" in docPath
            $realPath = realpath($tryPath);
            if ($realPath === false) {
                continue;
            }

            // Check path is within input directory using trailing slash to prevent
            // prefix attacks (e.g., /docs vs /docs-internal)
            if (!str_starts_with($realPath . '/', $realInputDir . '/')) {
                // Path traversal attempt detected - path is outside input directory
                continue;
            }

            return $realPath;
        }

        return null;
    }

    /**
     * Collect all anchors defined in this document.
     *
     * @return array<string, string> Anchor name => title
     */
    private function collectAnchors(DocumentNode $document, ProjectNode $projectNode): array
    {
        $anchors = [];
        $filePath = $document->getFilePath();

        // Get all internal targets from the project node for this document
        $allTargets = $projectNode->getAllInternalTargets();

        foreach ($allTargets as $targets) {
            foreach ($targets as $anchorName => $target) {
                if ($target->getDocumentPath() !== $filePath) {
                    continue;
                }

                $anchors[(string) $anchorName] = $target->getTitle() ?? (string) $anchorName;
            }
        }

        return $anchors;
    }

    /**
     * Collect section titles from this document.
     *
     * @return array<string, string> Section ID => title
     */
    private function collectSectionTitles(DocumentNode $document): array
    {
        $titles = [];

        $this->traverseNodes(array_values($document->getChildren()), static function (Node $node) use (&$titles): void {
            if (!($node instanceof SectionNode)) {
                return;
            }

            $titles[$node->getId()] = $node->getTitle()->toString();
        });

        return $titles;
    }

    /**
     * Collect citations defined in this document.
     *
     * @return string[]
     */
    private function collectCitations(DocumentNode $document): array
    {
        $citations = [];

        $this->traverseNodes(array_values($document->getChildren()), static function (Node $node) use (&$citations): void {
            if (!($node instanceof CitationNode)) {
                return;
            }

            $citations[] = $node->getName();
        });

        return $citations;
    }
}
