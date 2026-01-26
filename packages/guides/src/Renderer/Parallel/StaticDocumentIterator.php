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

use Iterator;
use OutOfRangeException;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Renderer\DocumentListIterator;

/**
 * A document iterator with pre-computed prev/next relationships.
 *
 * This iterator is designed for parallel rendering where we need to know
 * the prev/next relationships for ALL documents before forking, so each
 * child process can render its batch while maintaining correct navigation.
 *
 * Unlike DocumentListIterator which computes prev/next lazily as you iterate,
 * this iterator uses a pre-computed ordered list to determine relationships.
 *
 * @implements Iterator<array-key, DocumentNode>
 */
final class StaticDocumentIterator implements Iterator
{
    private int $position = 0;

    /** @var array<string, int> Map of document path to position in ordered list */
    private array $positionMap = [];

    /**
     * @param DocumentNode[] $orderedDocuments Full ordered list of all documents
     * @param DocumentNode[] $batchDocuments Documents to iterate (subset for parallel rendering)
     */
    public function __construct(
        private readonly array $orderedDocuments,
        private readonly array $batchDocuments,
    ) {
        // Build position map for O(1) lookup
        foreach ($orderedDocuments as $index => $doc) {
            $this->positionMap[$doc->getFilePath()] = $index;
        }
    }

    /**
     * Create from an existing DocumentListIterator.
     *
     * Iterates through the original iterator to capture the full document order,
     * then creates a static iterator that can be used in parallel contexts.
     *
     * @param DocumentNode[] $batchDocuments Documents to iterate (can be full list or subset)
     */
    public static function fromIterator(
        DocumentListIterator $iterator,
        array $batchDocuments,
    ): self {
        // Capture full document order from the iterator
        $orderedDocuments = [];
        foreach ($iterator as $document) {
            $orderedDocuments[] = $document;
        }

        return new self($orderedDocuments, $batchDocuments);
    }

    /**
     * Get the previous document in the FULL ordered list (not just the batch).
     */
    public function previousNode(): DocumentNode|null
    {
        $current = $this->batchDocuments[$this->position] ?? null;
        if ($current === null) {
            return null;
        }

        $globalPosition = $this->positionMap[$current->getFilePath()] ?? null;
        if ($globalPosition === null || $globalPosition === 0) {
            return null;
        }

        return $this->orderedDocuments[$globalPosition - 1] ?? null;
    }

    /**
     * Get the next document in the FULL ordered list (not just the batch).
     */
    public function nextNode(): DocumentNode|null
    {
        $current = $this->batchDocuments[$this->position] ?? null;
        if ($current === null) {
            return null;
        }

        $globalPosition = $this->positionMap[$current->getFilePath()] ?? null;
        if ($globalPosition === null) {
            return null;
        }

        return $this->orderedDocuments[$globalPosition + 1] ?? null;
    }

    public function current(): DocumentNode
    {
        if (!isset($this->batchDocuments[$this->position])) {
            throw new OutOfRangeException('No current document available');
        }

        return $this->batchDocuments[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->batchDocuments[$this->position]);
    }

    /**
     * Get all documents in the full ordered list.
     *
     * @return DocumentNode[]
     */
    public function getOrderedDocuments(): array
    {
        return $this->orderedDocuments;
    }

    /**
     * Get the batch documents this iterator will iterate over.
     *
     * @return DocumentNode[]
     */
    public function getBatchDocuments(): array
    {
        return $this->batchDocuments;
    }
}
