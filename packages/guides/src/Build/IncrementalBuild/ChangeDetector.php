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

use function array_flip;
use function array_keys;
use function filemtime;

/**
 * Detects which source files have changed since the last build.
 *
 * Uses timestamp-first checking for performance:
 * 1. If mtime unchanged → file unchanged (fast path)
 * 2. If mtime changed → compute hash to verify actual content change
 *
 * Known limitation: There is a small race condition window between the mtime
 * check and hash computation. A file modified during this window could be
 * incorrectly classified. This is acceptable for build systems where
 * concurrent modifications during builds are uncommon. For critical
 * applications, consider file locking or a two-phase verification.
 */
final class ChangeDetector
{
    /** Number of files checked via fast path (mtime only) */
    private int $fastPathHits = 0;

    /** Number of files requiring hash computation */
    private int $hashComputations = 0;

    public function __construct(
        private readonly ContentHasher $hasher,
    ) {
    }

    /**
     * Compare current document paths against cached state with a file path resolver.
     *
     * @param string[] $documentPaths Document paths (without extension)
     * @param array<string, DocumentExports> $cachedExports Previous build's exports
     * @param callable(string): string $fileResolver Resolves document path to actual file path
     */
    public function detectChangesWithResolver(
        array $documentPaths,
        array $cachedExports,
        callable $fileResolver,
    ): ChangeDetectionResult {
        $dirty = [];
        $clean = [];
        $new = [];
        $this->fastPathHits = 0;
        $this->hashComputations = 0;

        foreach ($documentPaths as $docPath) {
            $filePath = $fileResolver($docPath);
            $cached = $cachedExports[$docPath] ?? null;

            if ($cached === null) {
                $new[] = $docPath;
                continue;
            }

            // Timestamp-first optimization
            $currentMtime = $this->getFileMtime($filePath);

            if ($currentMtime === $cached->lastModified && $cached->lastModified > 0) {
                // Fast path: timestamp unchanged, assume content unchanged
                $this->fastPathHits++;
                $clean[] = $docPath;
                continue;
            }

            // Timestamp changed - verify with content hash
            $this->hashComputations++;
            $currentHash = $this->hasher->hashFile($filePath);

            if ($currentHash === $cached->contentHash) {
                // Content same despite mtime change (git checkout, touch, etc.)
                $clean[] = $docPath;
            } else {
                $dirty[] = $docPath;
            }
        }

        // Detect deleted files
        $deleted = [];
        $currentSet = array_flip($documentPaths);
        foreach (array_keys($cachedExports) as $cachedPath) {
            if (isset($currentSet[$cachedPath])) {
                continue;
            }

            $deleted[] = $cachedPath;
        }

        return new ChangeDetectionResult($dirty, $clean, $new, $deleted);
    }

    /**
     * Compare current source files against cached state (legacy method).
     *
     * @param string[] $sourceFiles Current source file paths
     * @param array<string, DocumentExports> $cachedExports Previous build's exports
     */
    public function detectChanges(array $sourceFiles, array $cachedExports): ChangeDetectionResult
    {
        return $this->detectChangesWithResolver($sourceFiles, $cachedExports, static fn ($path) => $path);
    }

    /**
     * Quick check if a single file has changed using timestamp-first approach.
     */
    public function hasFileChanged(string $filePath, DocumentExports|null $cached): bool
    {
        if ($cached === null) {
            return true;
        }

        // Timestamp-first check
        $currentMtime = $this->getFileMtime($filePath);
        if ($currentMtime === $cached->lastModified && $cached->lastModified > 0) {
            return false;
        }

        // Verify with hash
        $currentHash = $this->hasher->hashFile($filePath);

        return $currentHash !== $cached->contentHash;
    }

    /**
     * Get file modification time.
     *
     * Uses @ error suppression to avoid TOCTOU race between existence check
     * and mtime retrieval. If file doesn't exist or was deleted, returns 0.
     */
    public function getFileMtime(string $filePath): int
    {
        // Suppress warning if file vanishes; filemtime returns false for non-existent files
        $mtime = @filemtime($filePath);

        return $mtime !== false ? $mtime : 0;
    }

    /**
     * Get performance statistics for last detection run.
     *
     * @return array{fastPathHits: int, hashComputations: int}
     */
    public function getStats(): array
    {
        return [
            'fastPathHits' => $this->fastPathHits,
            'hashComputations' => $this->hashComputations,
        ];
    }
}
