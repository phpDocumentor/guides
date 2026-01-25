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

use InvalidArgumentException;

use function count;
use function get_debug_type;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function sprintf;

/**
 * Holds state for incremental builds within a single compilation run.
 *
 * This service stores the DependencyGraph and DocumentExports during compilation.
 * Consumer applications are responsible for persisting/loading this state between builds.
 */
final class IncrementalBuildState
{
    /**
     * Maximum number of exports allowed to prevent memory exhaustion.
     * Matches DependencyGraph::MAX_DOCUMENTS for consistency.
     */
    private const MAX_EXPORTS = 100_000;

    /**
     * Allowed hash algorithms for validation.
     * Must match algorithms supported by ContentHasher.
     */
    private const ALLOWED_ALGORITHMS = ['xxh128', 'sha256'];

    private DependencyGraph $dependencyGraph;

    /** @var array<string, DocumentExports> Document path => exports */
    private array $exports = [];

    /** @var array<string, DocumentExports> Cached exports from previous build */
    private array $previousExports = [];

    /** Directory containing input documentation files */
    private string $inputDir = '';

    /** Hash algorithm used when state was serialized (for compatibility checking) */
    private string $hashAlgorithm = '';

    public function __construct()
    {
        $this->dependencyGraph = new DependencyGraph();
    }

    /**
     * Get the dependency graph for this build.
     */
    public function getDependencyGraph(): DependencyGraph
    {
        return $this->dependencyGraph;
    }

    /**
     * Set the dependency graph (e.g., loaded from cache).
     */
    public function setDependencyGraph(DependencyGraph $graph): void
    {
        $this->dependencyGraph = $graph;
    }

    /**
     * Set exports for a document.
     *
     * Enforces runtime limits to prevent memory exhaustion during build.
     *
     * @throws InvalidArgumentException If adding a new document would exceed MAX_EXPORTS limit
     */
    public function setExports(string $documentPath, DocumentExports $exports): void
    {
        // Allow updating existing documents without counting against the limit
        if (!isset($this->exports[$documentPath]) && count($this->exports) >= self::MAX_EXPORTS) {
            throw new InvalidArgumentException(sprintf(
                'IncrementalBuildState: exports exceed maximum of %d documents',
                self::MAX_EXPORTS,
            ));
        }

        $this->exports[$documentPath] = $exports;
    }

    /**
     * Get exports for a document.
     */
    public function getExports(string $documentPath): DocumentExports|null
    {
        return $this->exports[$documentPath] ?? null;
    }

    /**
     * Get all current exports.
     *
     * Note: Returns the internal array. While PHP uses copy-on-write semantics,
     * callers should not rely on modifications to the returned array affecting
     * internal state. DocumentExports objects are immutable (readonly properties).
     *
     * @return array<string, DocumentExports>
     */
    public function getAllExports(): array
    {
        return $this->exports;
    }

    /**
     * Set exports from a previous build (for change detection).
     *
     * @param array<string, DocumentExports> $exports
     *
     * @throws InvalidArgumentException If exports exceed maximum allowed size
     */
    public function setPreviousExports(array $exports): void
    {
        if (count($exports) > self::MAX_EXPORTS) {
            throw new InvalidArgumentException(sprintf(
                'IncrementalBuildState: previousExports exceed maximum of %d documents',
                self::MAX_EXPORTS,
            ));
        }

        $this->previousExports = $exports;
    }

    /**
     * Get exports from the previous build.
     *
     * @return array<string, DocumentExports>
     */
    public function getPreviousExports(): array
    {
        return $this->previousExports;
    }

    /**
     * Get previous exports for a specific document.
     */
    public function getPreviousExportsFor(string $documentPath): DocumentExports|null
    {
        return $this->previousExports[$documentPath] ?? null;
    }

    /**
     * Set the input directory.
     */
    public function setInputDir(string $inputDir): void
    {
        $this->inputDir = $inputDir;
    }

    /**
     * Get the input directory.
     */
    public function getInputDir(): string
    {
        return $this->inputDir;
    }

    /**
     * Set the hash algorithm used for this state.
     *
     * Should be called when creating new state to record the current algorithm.
     *
     * @throws InvalidArgumentException If algorithm is not in ALLOWED_ALGORITHMS
     */
    public function setHashAlgorithm(string $algorithm): void
    {
        if (!in_array($algorithm, self::ALLOWED_ALGORITHMS, true)) {
            throw new InvalidArgumentException(sprintf(
                'IncrementalBuildState: invalid hash algorithm "%s", allowed: %s',
                $algorithm,
                implode(', ', self::ALLOWED_ALGORITHMS),
            ));
        }

        $this->hashAlgorithm = $algorithm;
    }

    /**
     * Get the hash algorithm used when this state was created/serialized.
     *
     * Returns empty string if algorithm was not recorded (legacy state).
     */
    public function getHashAlgorithm(): string
    {
        return $this->hashAlgorithm;
    }

    /**
     * Check if this state's hash algorithm is compatible with the given algorithm.
     *
     * Returns true if:
     * - The algorithms match exactly, OR
     * - This state has no recorded algorithm (legacy state created with sha256)
     *   AND the current algorithm is sha256
     *
     * Returns false if algorithms differ, indicating cached hashes are invalid
     * and a full rebuild is needed.
     *
     * Note: Legacy state (empty hashAlgorithm) assumes sha256 because it was the
     * only algorithm available before xxh128 support was added. This ensures
     * correct cache invalidation when xxh128 becomes available on a system.
     */
    public function isHashAlgorithmCompatible(string $currentAlgorithm): bool
    {
        // Legacy state without recorded algorithm - assume sha256 was used
        // (sha256 was the only option before xxh128 support was added)
        if ($this->hashAlgorithm === '') {
            return $currentAlgorithm === 'sha256';
        }

        return $this->hashAlgorithm === $currentAlgorithm;
    }

    /**
     * Reset state for a new build.
     */
    public function reset(): void
    {
        $this->dependencyGraph = new DependencyGraph();
        $this->exports = [];
        // Note: previousExports is intentionally NOT reset - it's the reference for change detection
    }

    /**
     * Serialize state to array for persistence.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $exportsArray = [];
        foreach ($this->exports as $path => $exports) {
            $exportsArray[$path] = $exports->toArray();
        }

        return [
            'dependencyGraph' => $this->dependencyGraph->toArray(),
            'exports' => $exportsArray,
            'hashAlgorithm' => $this->hashAlgorithm,
        ];
    }

    /**
     * Restore state from array.
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException If data format is invalid
     */
    public static function fromArray(array $data): self
    {
        $state = new self();

        if (isset($data['dependencyGraph'])) {
            $graphData = $data['dependencyGraph'];
            if (!is_array($graphData)) {
                throw new InvalidArgumentException(sprintf(
                    'IncrementalBuildState: expected dependencyGraph to be array, got %s',
                    get_debug_type($graphData),
                ));
            }

            $state->dependencyGraph = DependencyGraph::fromArray($graphData);
        }

        if (isset($data['exports'])) {
            $exportsData = $data['exports'];
            if (!is_array($exportsData)) {
                throw new InvalidArgumentException(sprintf(
                    'IncrementalBuildState: expected exports to be array, got %s',
                    get_debug_type($exportsData),
                ));
            }

            // Enforce size limit to prevent memory exhaustion
            if (count($exportsData) > self::MAX_EXPORTS) {
                throw new InvalidArgumentException(sprintf(
                    'IncrementalBuildState: exports exceed maximum of %d documents',
                    self::MAX_EXPORTS,
                ));
            }

            foreach ($exportsData as $path => $exportData) {
                // PHP array keys are always int or string, so just validate value
                if (!is_array($exportData)) {
                    throw new InvalidArgumentException(sprintf(
                        'IncrementalBuildState: expected export data for "%s" to be array, got %s',
                        $path,
                        get_debug_type($exportData),
                    ));
                }

                $state->exports[(string) $path] = DocumentExports::fromArray($exportData);
            }
        }

        // Restore hash algorithm if present (may be empty for legacy state)
        if (isset($data['hashAlgorithm'])) {
            $algorithm = $data['hashAlgorithm'];
            if (!is_string($algorithm)) {
                throw new InvalidArgumentException(sprintf(
                    'IncrementalBuildState: expected hashAlgorithm to be string, got %s',
                    get_debug_type($algorithm),
                ));
            }

            // Validate algorithm if not empty (empty is valid for legacy state)
            if ($algorithm !== '' && !in_array($algorithm, self::ALLOWED_ALGORITHMS, true)) {
                throw new InvalidArgumentException(sprintf(
                    'IncrementalBuildState: invalid hash algorithm "%s", allowed: %s',
                    $algorithm,
                    implode(', ', self::ALLOWED_ALGORITHMS),
                ));
            }

            $state->hashAlgorithm = $algorithm;
        }

        return $state;
    }
}
