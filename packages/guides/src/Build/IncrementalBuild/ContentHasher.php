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

use RuntimeException;

use function basename;
use function file_exists;
use function hash;
use function hash_algos;
use function hash_file;
use function in_array;
use function json_encode;
use function ksort;
use function sort;
use function sprintf;

use const JSON_THROW_ON_ERROR;

/**
 * Fast content hashing utility for change detection.
 *
 * Uses xxHash when available for speed, falls back to SHA-256.
 */
final class ContentHasher
{
    private readonly string $algorithm;

    public function __construct()
    {
        // Algorithm selection priority:
        // - xxh128: ~10x faster than SHA-256, sufficient for change detection (non-cryptographic use)
        // - sha256: Built-in PHP algorithm, always available as fallback
        $this->algorithm = in_array('xxh128', hash_algos(), true) ? 'xxh128' : 'sha256';
    }

    /**
     * Hash a file's contents.
     *
     * Returns empty string if file doesn't exist (expected case for new documents).
     *
     * Note: Uses @ error suppression to avoid TOCTOU race condition between
     * file_exists() and hash_file(). The hash_file() call handles non-existent
     * files by returning false, which we then check.
     *
     * @throws RuntimeException If hashing fails for an existing, readable file
     */
    public function hashFile(string $filePath): string
    {
        // Suppress warnings from hash_file() for non-existent files to avoid
        // TOCTOU race between file_exists() check and hash_file() call.
        // If file doesn't exist or was deleted, hash_file returns false.
        $hash = @hash_file($this->algorithm, $filePath);

        if ($hash === false) {
            // Check if file exists now - if not, return empty (expected for new docs)
            // This is a race-condition-safe approach: we try first, check existence after
            if (!file_exists($filePath)) {
                return '';
            }

            // File exists but hashing failed (permissions, I/O error, etc.)
            // Use basename to avoid leaking full system paths in error messages
            throw new RuntimeException(sprintf(
                'ContentHasher: failed to hash file "%s" with algorithm "%s"',
                basename($filePath),
                $this->algorithm,
            ));
        }

        return $hash;
    }

    /**
     * Hash arbitrary string content.
     */
    public function hashContent(string $content): string
    {
        return hash($this->algorithm, $content);
    }

    /**
     * Compute hash of document exports for dependency invalidation.
     *
     * @param array<string, mixed> $anchors
     * @param array<string, string> $sectionTitles
     * @param string[] $citations
     * @param string $documentTitle Required to ensure consistent hashing
     */
    public function hashExports(
        array $anchors,
        array $sectionTitles,
        array $citations,
        string $documentTitle,
    ): string {
        // Sort keys for deterministic hashing
        ksort($anchors);
        ksort($sectionTitles);
        sort($citations);

        $data = json_encode([
            'anchors' => $anchors,
            'sectionTitles' => $sectionTitles,
            'citations' => $citations,
            'documentTitle' => $documentTitle,
        ], JSON_THROW_ON_ERROR);

        return hash($this->algorithm, $data);
    }

    /**
     * Get the algorithm being used.
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }
}
