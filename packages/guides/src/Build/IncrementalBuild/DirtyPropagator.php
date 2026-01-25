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

use SplQueue;

use function array_diff;
use function array_flip;
use function array_keys;
use function array_merge;
use function array_unique;
use function array_values;
use function count;

/**
 * Propagates dirty state through the dependency graph.
 *
 * When a document's exports change, all documents that import from it
 * must also be re-rendered to update their cross-references.
 *
 * Uses SplQueue for O(1) queue operations instead of array_shift which is O(n).
 */
final class DirtyPropagator
{
    /**
     * Maximum visited documents during propagation (defense-in-depth).
     * Consistent with PropagationResult::MAX_DOCUMENTS and IncrementalBuildCache::MAX_EXPORTS.
     */
    private const MAX_PROPAGATION_VISITS = 100_000;

    /**
     * Propagate dirty state and compute final render set.
     *
     * @param ChangeDetectionResult $changes Initial change detection
     * @param DependencyGraph $graph Dependency relationships
     * @param array<string, DocumentExports> $oldExports Previous build's exports
     * @param array<string, DocumentExports> $newExports Current build's exports (for dirty docs)
     */
    public function propagate(
        ChangeDetectionResult $changes,
        DependencyGraph $graph,
        array $oldExports,
        array $newExports,
    ): PropagationResult {
        // Start with directly dirty/new documents
        $dirtySet = array_flip(array_merge($changes->dirty, $changes->new));
        $propagatedFrom = [];

        // Handle deleted files - their dependents become dirty
        foreach ($changes->deleted as $deletedPath) {
            $dependents = $graph->getDependents($deletedPath);
            foreach ($dependents as $dependent) {
                if (isset($dirtySet[$dependent])) {
                    continue;
                }

                $dirtySet[$dependent] = true;
                $propagatedFrom[] = $deletedPath;
            }
        }

        // Check if exports changed for dirty docs
        // If so, propagate to dependents
        /** @var SplQueue<string> $queue */
        $queue = new SplQueue();
        foreach (array_keys($dirtySet) as $doc) {
            $queue->enqueue($doc);
        }

        $visited = [];

        while (!$queue->isEmpty()) {
            $current = $queue->dequeue();

            if (isset($visited[$current])) {
                continue;
            }

            $visited[$current] = true;

            // Defense-in-depth: prevent runaway propagation
            if (count($visited) >= self::MAX_PROPAGATION_VISITS) {
                break;
            }

            // Check if exports changed
            $old = $oldExports[$current] ?? null;
            $new = $newExports[$current] ?? null;

            $exportsChanged = false;
            if ($old === null || $new === null) {
                // New or deleted - definitely changed
                $exportsChanged = true;
            } elseif ($old->hasExportsChanged($new)) {
                $exportsChanged = true;
            }

            if (!$exportsChanged) {
                continue;
            }

            // Propagate to dependents
            foreach ($graph->getDependents($current) as $dependent) {
                if (isset($dirtySet[$dependent])) {
                    continue;
                }

                $dirtySet[$dependent] = true;
                $propagatedFrom[] = $current;

                // Add to queue for further propagation
                if (isset($visited[$dependent])) {
                    continue;
                }

                $queue->enqueue($dependent);
            }
        }

        // Compute final sets
        $documentsToRender = array_keys($dirtySet);
        $documentsToSkip = array_diff($changes->clean, $documentsToRender);

        return new PropagationResult(
            documentsToRender: array_values($documentsToRender),
            documentsToSkip: array_values($documentsToSkip),
            propagatedFrom: array_unique($propagatedFrom),
        );
    }

    /**
     * Simple propagation without export comparison.
     * Used when exports aren't available yet (during initial compile).
     *
     * @param string[] $dirtyDocs Initially dirty documents
     * @param DependencyGraph $graph Dependency relationships
     *
     * @return string[] All documents that need rendering
     */
    public function propagateSimple(array $dirtyDocs, DependencyGraph $graph): array
    {
        return $graph->propagateDirty($dirtyDocs);
    }
}
