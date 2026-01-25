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

namespace phpDocumentor\Guides\Build\IncrementalBuild;

use Generator;
use InvalidArgumentException;
use SplQueue;

use function array_keys;
use function array_map;
use function assert;
use function count;
use function get_debug_type;
use function is_array;
use function is_string;
use function max;
use function sprintf;

/**
 * Tracks inter-document dependencies for incremental rendering.
 *
 * When document A references an anchor in document B:
 * - A "imports" from B
 * - B has A as a "dependent"
 *
 * If B's exports change, A needs to be re-rendered.
 *
 * Thread Safety:
 * This class is NOT thread-safe. All methods that modify state (addImport,
 * removeDocument, clearImportsFor, merge) must be called from a single thread.
 * Read-only methods (getImports, getDependents, propagateDirty, toArray) can be
 * called concurrently only when no modifications are in progress.
 */
final class DependencyGraph
{
    /** Maximum number of documents allowed in the graph to prevent memory exhaustion */
    private const MAX_DOCUMENTS = 100_000;

    /** Maximum number of imports per document to prevent memory exhaustion */
    private const MAX_IMPORTS_PER_DOCUMENT = 1000;

    /**
     * Maximum total number of edges allowed in the graph to prevent memory exhaustion.
     *
     * This provides a global complexity limit independent of per-document limits.
     * With ~64 bytes per edge (key + value overhead), 2M edges ≈ 128-256MB RAM.
     */
    private const MAX_TOTAL_EDGES = 2_000_000;

    /** Current total number of edges in the graph */
    private int $edgeCount = 0;

    /**
     * Forward edges: docPath -> [imported docPath => true]
     * "Document A imports from documents B, C, D"
     * Uses keyed arrays for O(1) lookup instead of in_array O(n).
     *
     * @var array<string, array<string, true>>
     */
    private array $imports = [];

    /**
     * Reverse edges: docPath -> [dependent docPath => true]
     * "Document B is depended on by documents A, E, F"
     * Uses keyed arrays for O(1) lookup instead of in_array O(n).
     *
     * @var array<string, array<string, true>>
     */
    private array $dependents = [];

    /**
     * Record that $fromDoc imports/references something from $toDoc.
     * O(1) operation using keyed arrays.
     *
     * Enforces runtime limits to prevent memory exhaustion during build:
     * - MAX_DOCUMENTS: Maximum total documents in the graph
     * - MAX_IMPORTS_PER_DOCUMENT: Maximum imports per document
     *
     * Returns false if the import was rejected due to limits being reached.
     */
    public function addImport(string $fromDoc, string $toDoc): bool
    {
        // Don't add self-references
        if ($fromDoc === $toDoc) {
            return true; // Not an error, just a no-op
        }

        // Check if this edge already exists (no limits needed for existing edges)
        if (isset($this->imports[$fromDoc][$toDoc])) {
            return true;
        }

        // Enforce global edge limit to prevent memory exhaustion
        if ($this->edgeCount >= self::MAX_TOTAL_EDGES) {
            return false;
        }

        // Enforce per-document import limit
        if (isset($this->imports[$fromDoc]) && count($this->imports[$fromDoc]) >= self::MAX_IMPORTS_PER_DOCUMENT) {
            return false;
        }

        // Enforce total document limit (only check when adding a new document)
        if (!isset($this->imports[$fromDoc]) && count($this->imports) >= self::MAX_DOCUMENTS) {
            return false;
        }

        // Add forward edge (O(1) with isset check)
        $this->imports[$fromDoc][$toDoc] = true;

        // Add reverse edge (O(1) with isset check)
        $this->dependents[$toDoc][$fromDoc] = true;

        // Track total edges
        $this->edgeCount++;

        return true;
    }

    /**
     * Get all documents that $docPath imports from.
     *
     * Note: Uses array_map to ensure string return type since PHP converts
     * numeric string keys to integers in arrays.
     *
     * @return string[]
     */
    public function getImports(string $docPath): array
    {
        return array_map('strval', array_keys($this->imports[$docPath] ?? []));
    }

    /**
     * Get all documents that depend on $docPath.
     *
     * Note: Uses array_map to ensure string return type since PHP converts
     * numeric string keys to integers in arrays.
     *
     * @return string[]
     */
    public function getDependents(string $docPath): array
    {
        return array_map('strval', array_keys($this->dependents[$docPath] ?? []));
    }

    /**
     * Given a set of dirty documents, propagate to find all affected documents.
     * Uses transitive closure: if A depends on B, and B is dirty, A is dirty.
     *
     * Optimized to O(V+E) using SplQueue for O(1) dequeue operations.
     *
     * Note on memory usage: This method builds a complete result array in memory.
     * For very large graphs (e.g., 100k+ documents), consider using
     * propagateDirtyIterator() which yields results one at a time.
     *
     * @param string[] $dirtyDocs Initially dirty documents
     *
     * @return string[] All documents that need re-rendering
     */
    public function propagateDirty(array $dirtyDocs): array
    {
        $result = [];
        $visited = [];

        // Use SplQueue for O(1) enqueue/dequeue instead of array_shift O(n)
        /** @var SplQueue<string> $queue */
        $queue = new SplQueue();
        foreach ($dirtyDocs as $doc) {
            $queue->enqueue($doc);
        }

        while (!$queue->isEmpty()) {
            $current = $queue->dequeue();
            assert(is_string($current));

            if (isset($visited[$current])) {
                continue;
            }

            $visited[$current] = true;
            $result[] = $current;

            // Add all dependents to the queue
            foreach ($this->getDependents($current) as $dependent) {
                if (isset($visited[$dependent])) {
                    continue;
                }

                $queue->enqueue($dependent);
            }
        }

        return $result;
    }

    /**
     * Generator version of propagateDirty for memory-efficient processing of large graphs.
     *
     * Yields documents one at a time instead of building a full result array.
     * Use this for very large dependency graphs where memory is a concern.
     *
     * @param string[] $dirtyDocs Initially dirty documents
     *
     * @return Generator<int, string>
     */
    public function propagateDirtyIterator(array $dirtyDocs): Generator
    {
        $visited = [];

        /** @var SplQueue<string> $queue */
        $queue = new SplQueue();
        foreach ($dirtyDocs as $doc) {
            $queue->enqueue($doc);
        }

        while (!$queue->isEmpty()) {
            $current = $queue->dequeue();
            assert(is_string($current));

            if (isset($visited[$current])) {
                continue;
            }

            $visited[$current] = true;

            yield $current;

            foreach ($this->getDependents($current) as $dependent) {
                if (isset($visited[$dependent])) {
                    continue;
                }

                $queue->enqueue($dependent);
            }
        }
    }

    /**
     * Remove a document from the graph (when deleted).
     * O(k) where k is edges involving this document (uses reverse index for efficiency).
     */
    public function removeDocument(string $docPath): void
    {
        // 1. Remove edges pointing TO this document (using reverse index for O(k) instead of O(N))
        // The dependents index tells us which documents import this one
        $parents = array_keys($this->dependents[$docPath] ?? []);
        foreach ($parents as $from) {
            if (!isset($this->imports[$from][$docPath])) {
                continue;
            }

            unset($this->imports[$from][$docPath]);
            $this->edgeCount--;

            if ($this->imports[$from] !== []) {
                continue;
            }

            unset($this->imports[$from]);
        }

        // 2. Remove edges originating FROM this document
        $children = $this->imports[$docPath] ?? [];
        $this->edgeCount -= count($children);

        // Remove this document from the dependents list of its imports
        foreach (array_keys($children) as $to) {
            unset($this->dependents[$to][$docPath]);
            if (!isset($this->dependents[$to]) || $this->dependents[$to] !== []) {
                continue;
            }

            unset($this->dependents[$to]);
        }

        // 3. Remove own entries
        unset($this->imports[$docPath]);
        unset($this->dependents[$docPath]);

        // Safety: ensure non-negative edge count
        if ($this->edgeCount >= 0) {
            return;
        }

        $this->edgeCount = 0;
    }

    /**
     * Clear all edges for a document (before re-computing).
     * O(I) where I is number of imports for this document.
     */
    public function clearImportsFor(string $docPath): void
    {
        $oldImports = $this->imports[$docPath] ?? [];
        $edgesRemoved = count($oldImports);
        unset($this->imports[$docPath]);

        // Remove this doc from dependents of its old imports
        foreach (array_keys($oldImports) as $importedDoc) {
            unset($this->dependents[$importedDoc][$docPath]);
            if (!isset($this->dependents[$importedDoc]) || $this->dependents[$importedDoc] !== []) {
                continue;
            }

            unset($this->dependents[$importedDoc]);
        }

        // Update edge count
        $this->edgeCount -= $edgesRemoved;
        if ($this->edgeCount >= 0) {
            return;
        }

        $this->edgeCount = 0; // Safety: ensure non-negative
    }

    /**
     * Get all document paths in the graph.
     *
     * @return string[]
     */
    public function getAllDocuments(): array
    {
        // Use array union for O(1) uniqueness per key instead of array_unique O(n log n)
        // array_map ensures string return type (PHP converts numeric keys to int)
        return array_map('strval', array_keys($this->imports + $this->dependents));
    }

    /**
     * Serialize to array for JSON persistence.
     *
     * Note: Keys are explicitly cast to strings for consistent serialization,
     * since PHP converts numeric string keys to integers in arrays.
     *
     * @return array{imports: array<string, string[]>, dependents: array<string, string[]>}
     */
    public function toArray(): array
    {
        $imports = [];
        foreach ($this->imports as $from => $toMap) {
            // Cast keys to strings for consistent serialization
            $imports[(string) $from] = array_map('strval', array_keys($toMap));
        }

        $dependents = [];
        foreach ($this->dependents as $to => $fromMap) {
            // Cast keys to strings for consistent serialization
            $dependents[(string) $to] = array_map('strval', array_keys($fromMap));
        }

        return [
            'imports' => $imports,
            'dependents' => $dependents,
        ];
    }

    /**
     * Deserialize from array.
     *
     * Security: This method validates all input to protect against maliciously crafted
     * cache files. It enforces size limits (MAX_DOCUMENTS, MAX_IMPORTS_PER_DOCUMENT)
     * to prevent memory exhaustion attacks from corrupted or attacker-controlled JSON.
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException If data format is invalid or size limits exceeded
     */
    public static function fromArray(array $data): self
    {
        $graph = new self();

        $imports = $data['imports'] ?? [];
        if (!is_array($imports)) {
            throw new InvalidArgumentException(sprintf(
                'DependencyGraph: expected imports to be array, got %s',
                get_debug_type($imports),
            ));
        }

        // Enforce document count limit
        if (count($imports) > self::MAX_DOCUMENTS) {
            throw new InvalidArgumentException(sprintf(
                'DependencyGraph: imports exceed maximum of %d documents',
                self::MAX_DOCUMENTS,
            ));
        }

        foreach ($imports as $from => $toList) {
            // Convert PHP integer keys (from JSON numeric strings) to string
            $fromKey = (string) $from;

            if (!is_array($toList)) {
                throw new InvalidArgumentException(sprintf(
                    'DependencyGraph: expected import value for "%s" to be array, got %s',
                    $fromKey,
                    get_debug_type($toList),
                ));
            }

            // Enforce per-document import limit
            if (count($toList) > self::MAX_IMPORTS_PER_DOCUMENT) {
                throw new InvalidArgumentException(sprintf(
                    'DependencyGraph: imports for "%s" exceed maximum of %d',
                    $fromKey,
                    self::MAX_IMPORTS_PER_DOCUMENT,
                ));
            }

            // Validate all values in toList are strings
            $validated = [];
            foreach ($toList as $value) {
                if (!is_string($value)) {
                    throw new InvalidArgumentException(sprintf(
                        'DependencyGraph: expected import target for "%s" to be string, got %s',
                        $fromKey,
                        get_debug_type($value),
                    ));
                }

                $validated[$value] = true;
            }

            $graph->imports[$fromKey] = $validated;
        }

        $dependents = $data['dependents'] ?? [];
        if (!is_array($dependents)) {
            throw new InvalidArgumentException(sprintf(
                'DependencyGraph: expected dependents to be array, got %s',
                get_debug_type($dependents),
            ));
        }

        // Enforce document count limit for dependents
        if (count($dependents) > self::MAX_DOCUMENTS) {
            throw new InvalidArgumentException(sprintf(
                'DependencyGraph: dependents exceed maximum of %d documents',
                self::MAX_DOCUMENTS,
            ));
        }

        foreach ($dependents as $to => $fromList) {
            // Convert PHP integer keys (from JSON numeric strings) to string
            $toKey = (string) $to;

            if (!is_array($fromList)) {
                throw new InvalidArgumentException(sprintf(
                    'DependencyGraph: expected dependent value for "%s" to be array, got %s',
                    $toKey,
                    get_debug_type($fromList),
                ));
            }

            // Enforce per-document dependent limit
            if (count($fromList) > self::MAX_IMPORTS_PER_DOCUMENT) {
                throw new InvalidArgumentException(sprintf(
                    'DependencyGraph: dependents for "%s" exceed maximum of %d',
                    $toKey,
                    self::MAX_IMPORTS_PER_DOCUMENT,
                ));
            }

            // Validate all values in fromList are strings
            $validated = [];
            foreach ($fromList as $value) {
                if (!is_string($value)) {
                    throw new InvalidArgumentException(sprintf(
                        'DependencyGraph: expected dependent source for "%s" to be string, got %s',
                        $toKey,
                        get_debug_type($value),
                    ));
                }

                $validated[$value] = true;
            }

            $graph->dependents[$toKey] = $validated;
        }

        // Compute total edge count from imports
        $totalEdges = 0;
        foreach ($graph->imports as $toMap) {
            $totalEdges += count($toMap);
        }

        // Enforce total edge limit
        if ($totalEdges > self::MAX_TOTAL_EDGES) {
            throw new InvalidArgumentException(sprintf(
                'DependencyGraph: total edges (%d) exceed maximum of %d',
                $totalEdges,
                self::MAX_TOTAL_EDGES,
            ));
        }

        $graph->edgeCount = $totalEdges;

        return $graph;
    }

    /**
     * Get statistics about the graph.
     *
     * @return array{documents: int, edges: int, avgImportsPerDoc: float}
     */
    public function getStats(): array
    {
        $importCount = count($this->imports);

        return [
            'documents' => count($this->getAllDocuments()),
            'edges' => $this->edgeCount,
            'avgImportsPerDoc' => $this->edgeCount > 0 ? (float) ($this->edgeCount / max(1, $importCount)) : 0.0,
        ];
    }

    /**
     * Merge another dependency graph into this one.
     * Used to combine results from parallel child processes.
     *
     * Thread Safety: This method is NOT thread-safe. It should only be called
     * from a single-threaded context after child processes have completed and
     * returned their results. Do not call from multiple threads simultaneously.
     *
     * Size Limits: After merging, the resulting graph may exceed MAX_DOCUMENTS
     * or MAX_IMPORTS_PER_DOCUMENT limits. Call validateLimits() after merging
     * if strict limit enforcement is required.
     */
    public function merge(self $other): void
    {
        foreach ($other->imports as $from => $toMap) {
            if (!isset($this->imports[$from])) {
                $this->imports[$from] = $toMap;
            } else {
                $this->imports[$from] += $toMap;
            }
        }

        foreach ($other->dependents as $to => $fromMap) {
            if (!isset($this->dependents[$to])) {
                $this->dependents[$to] = $fromMap;
            } else {
                $this->dependents[$to] += $fromMap;
            }
        }

        // Recalculate edge count after merge
        $this->edgeCount = 0;
        foreach ($this->imports as $toMap) {
            $this->edgeCount += count($toMap);
        }
    }

    /**
     * Validate that the graph does not exceed size limits.
     *
     * Call this after merge() if strict limit enforcement is required.
     *
     * @throws InvalidArgumentException If any limits are exceeded
     */
    public function validateLimits(): void
    {
        // Validate imports
        if (count($this->imports) > self::MAX_DOCUMENTS) {
            throw new InvalidArgumentException(sprintf(
                'DependencyGraph: imports exceed maximum of %d documents',
                self::MAX_DOCUMENTS,
            ));
        }

        foreach ($this->imports as $from => $toMap) {
            if (count($toMap) > self::MAX_IMPORTS_PER_DOCUMENT) {
                throw new InvalidArgumentException(sprintf(
                    'DependencyGraph: imports for "%s" exceed maximum of %d',
                    $from,
                    self::MAX_IMPORTS_PER_DOCUMENT,
                ));
            }
        }

        // Validate dependents (reverse edges)
        if (count($this->dependents) > self::MAX_DOCUMENTS) {
            throw new InvalidArgumentException(sprintf(
                'DependencyGraph: dependents exceed maximum of %d documents',
                self::MAX_DOCUMENTS,
            ));
        }

        foreach ($this->dependents as $to => $fromMap) {
            if (count($fromMap) > self::MAX_IMPORTS_PER_DOCUMENT) {
                throw new InvalidArgumentException(sprintf(
                    'DependencyGraph: dependents for "%s" exceed maximum of %d',
                    $to,
                    self::MAX_IMPORTS_PER_DOCUMENT,
                ));
            }
        }

        // Validate total edge count
        if ($this->edgeCount > self::MAX_TOTAL_EDGES) {
            throw new InvalidArgumentException(sprintf(
                'DependencyGraph: total edges (%d) exceed maximum of %d',
                $this->edgeCount,
                self::MAX_TOTAL_EDGES,
            ));
        }
    }
}
