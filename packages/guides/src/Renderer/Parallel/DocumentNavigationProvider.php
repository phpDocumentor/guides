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

namespace phpDocumentor\Guides\Renderer\Parallel;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Renderer\DocumentListIterator;

use function array_values;
use function count;

/**
 * Provides pre-computed prev/next navigation for parallel rendering.
 *
 * When using parallel rendering with forked processes, each child process
 * only renders a subset of documents. The normal iterator-based prev/next
 * tracking doesn't work because the iterator state is per-process.
 *
 * This provider captures the full document order before forking and provides
 * prev/next lookup by document path, which works correctly across all
 * child processes.
 */
final class DocumentNavigationProvider
{
    /** @var array<string, DocumentNode> Map of path to document for the full ordered list */
    private array $documentsByPath = [];

    /** @var DocumentNode[] Full ordered list of documents */
    private array $orderedDocuments = [];

    /** @var array<string, int> Map of document path to position in ordered list */
    private array $positionMap = [];

    private bool $initialized = false;

    /**
     * Initialize the navigation map from a DocumentListIterator.
     *
     * This captures the full document order by iterating through the iterator.
     * Must be called BEFORE forking to capture the complete order.
     */
    public function initializeFromIterator(DocumentListIterator $iterator): void
    {
        $this->orderedDocuments = [];
        $this->documentsByPath = [];
        $this->positionMap = [];

        // Capture full document order
        foreach ($iterator as $document) {
            $path = $document->getFilePath();
            $position = count($this->orderedDocuments);

            $this->orderedDocuments[] = $document;
            $this->documentsByPath[$path] = $document;
            $this->positionMap[$path] = $position;
        }

        $this->initialized = true;
    }

    /**
     * Initialize from an already-ordered array of documents.
     *
     * @param DocumentNode[] $orderedDocuments
     */
    public function initializeFromArray(array $orderedDocuments): void
    {
        $this->orderedDocuments = array_values($orderedDocuments);
        $this->documentsByPath = [];
        $this->positionMap = [];

        foreach ($this->orderedDocuments as $position => $document) {
            $path = $document->getFilePath();
            $this->documentsByPath[$path] = $document;
            $this->positionMap[$path] = $position;
        }

        $this->initialized = true;
    }

    /**
     * Check if navigation data has been initialized.
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * Get the previous document for a given document path.
     */
    public function getPreviousDocument(string $currentPath): DocumentNode|null
    {
        if (!$this->initialized) {
            return null;
        }

        $position = $this->positionMap[$currentPath] ?? null;
        if ($position === null || $position === 0) {
            return null;
        }

        return $this->orderedDocuments[$position - 1] ?? null;
    }

    /**
     * Get the next document for a given document path.
     */
    public function getNextDocument(string $currentPath): DocumentNode|null
    {
        if (!$this->initialized) {
            return null;
        }

        $position = $this->positionMap[$currentPath] ?? null;
        if ($position === null) {
            return null;
        }

        return $this->orderedDocuments[$position + 1] ?? null;
    }

    /**
     * Get the full ordered document list.
     *
     * @return DocumentNode[]
     */
    public function getOrderedDocuments(): array
    {
        return $this->orderedDocuments;
    }

    /**
     * Get document by path.
     */
    public function getDocument(string $path): DocumentNode|null
    {
        return $this->documentsByPath[$path] ?? null;
    }

    /**
     * Get the total number of documents.
     */
    public function count(): int
    {
        return count($this->orderedDocuments);
    }

    /**
     * Clear the navigation data (useful for tests).
     */
    public function clear(): void
    {
        $this->orderedDocuments = [];
        $this->documentsByPath = [];
        $this->positionMap = [];
        $this->initialized = false;
    }
}
