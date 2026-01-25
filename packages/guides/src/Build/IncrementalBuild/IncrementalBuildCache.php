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
use JsonException;
use RuntimeException;

use function array_keys;
use function basename;
use function count;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function glob;
use function is_array;
use function is_dir;
use function is_string;
use function json_decode;
use function json_encode;
use function md5;
use function mkdir;
use function preg_match;
use function strlen;
use function substr;
use function unlink;

use const GLOB_ONLYDIR;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * Central cache for incremental build state.
 *
 * Cache is sharded for performance:
 * - _build_meta.json: Metadata, dependency graph, output paths (small, always loaded)
 * - _exports/<hash>/<docPath>.json: Per-document exports (loaded on demand)
 *
 * Sharding benefits:
 * - O(1) save per changed document instead of O(n) full rewrite
 * - Better git diffs (only changed files appear)
 * - Reduced memory for large projects (can load exports on demand)
 *
 * Security considerations:
 * - All input data is validated before use
 * - Maximum limits enforced on document counts
 * - Path traversal prevented in shard operations
 *
 * Thread safety:
 * - NOT thread-safe. Designed for single-threaded build processes.
 * - For parallel builds: each child process should use extractState()/mergeState()
 *   to serialize state, with the parent process merging results sequentially
 *   after all children complete.
 */
final class IncrementalBuildCache
{
    private const BUILD_META_FILE = '_build_meta.json';
    private const EXPORTS_DIR = '_exports';

    /**
     * Maximum number of exports.
     * Consistent with PropagationResult::MAX_DOCUMENTS and DirtyPropagator::MAX_PROPAGATION_VISITS.
     */
    private const MAX_EXPORTS = 100_000;

    /**
     * Maximum number of output path mappings.
     * Consistent with MAX_EXPORTS.
     */
    private const MAX_OUTPUT_PATHS = 100_000;

    /** @var array<string, DocumentExports> */
    private array $exports = [];

    private DependencyGraph $dependencyGraph;

    /** @var array<string, string> docPath -> rendered output path */
    private array $outputPaths = [];

    /** @var array<string, mixed> */
    private array $metadata = [];

    private bool $loaded = false;

    /** Input directory for file path resolution */
    private string $inputDir = '';

    /** @var array<string, true> Tracks which exports have been modified (for incremental save) */
    private array $dirtyExports = [];

    /** Output directory (stored for incremental saves) */
    private string|null $outputDir = null;

    public function __construct(
        private readonly CacheVersioning $versioning,
    ) {
        $this->dependencyGraph = new DependencyGraph();
    }

    /**
     * Load cache from output directory.
     *
     * Supports both legacy (monolithic) and sharded cache formats.
     *
     * @param string $outputDir The output directory where _build_meta.json is stored
     *
     * @return bool True if cache was loaded and is valid
     */
    public function load(string $outputDir): bool
    {
        $this->outputDir = $outputDir;
        $metaPath = $outputDir . '/' . self::BUILD_META_FILE;

        if (!file_exists($metaPath)) {
            return false;
        }

        $json = file_get_contents($metaPath);
        if ($json === false) {
            return false;
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        if (!is_array($data)) {
            return false;
        }

        // Load and validate metadata
        /** @var array<string, mixed> $metadata */
        $metadata = $data['metadata'] ?? [];
        if (!is_array($metadata)) {
            return false;
        }

        $this->metadata = $metadata;
        if (!$this->versioning->isCacheValid($this->metadata)) {
            return false;
        }

        // Check if using sharded exports (new format)
        $exportsDir = $outputDir . '/' . self::EXPORTS_DIR;
        $isSharded = is_dir($exportsDir) && !isset($data['exports']);

        if ($isSharded) {
            // Load exports from sharded files
            $this->loadShardedExports($exportsDir);
        } else {
            // Legacy: Load exports from main file
            $exportsData = $data['exports'] ?? [];
            if (!is_array($exportsData)) {
                return false;
            }

            if (count($exportsData) > self::MAX_EXPORTS) {
                throw new InvalidArgumentException('exports exceed maximum of ' . self::MAX_EXPORTS);
            }

            foreach ($exportsData as $path => $exportData) {
                if (!is_string($path) || !is_array($exportData)) {
                    continue;
                }

                $this->exports[$path] = DocumentExports::fromArray($exportData);
            }
        }

        // Load dependencies
        $depsData = $data['dependencies'] ?? [];
        if (!is_array($depsData)) {
            return false;
        }

        $this->dependencyGraph = DependencyGraph::fromArray($depsData);

        // Load output paths
        $outputPaths = $data['outputs'] ?? [];
        if (!is_array($outputPaths)) {
            return false;
        }

        if (count($outputPaths) > self::MAX_OUTPUT_PATHS) {
            throw new InvalidArgumentException('output paths exceed maximum of ' . self::MAX_OUTPUT_PATHS);
        }

        foreach ($outputPaths as $docPath => $outputPath) {
            if (!is_string($docPath) || !is_string($outputPath)) {
                continue;
            }

            $this->outputPaths[$docPath] = $outputPath;
        }

        $this->loaded = true;
        $this->dirtyExports = []; // Reset dirty tracking after load

        return true;
    }

    /**
     * Load exports from sharded directory structure.
     */
    private function loadShardedExports(string $exportsDir): void
    {
        // Iterate through shard directories (2-char hash prefixes)
        $shardDirs = glob($exportsDir . '/*', GLOB_ONLYDIR);
        if ($shardDirs === false) {
            return;
        }

        $loadedCount = 0;

        foreach ($shardDirs as $shardDir) {
            // Validate shard directory name (must be 2 hex chars)
            $shardName = basename($shardDir);
            if (!$this->isValidShardName($shardName)) {
                continue;
            }

            $files = glob($shardDir . '/*.json');
            if ($files === false) {
                continue;
            }

            foreach ($files as $file) {
                if ($loadedCount >= self::MAX_EXPORTS) {
                    throw new InvalidArgumentException('exports exceed maximum of ' . self::MAX_EXPORTS);
                }

                $json = file_get_contents($file);
                if ($json === false) {
                    continue;
                }

                try {
                    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    continue;
                }

                if (!is_array($data) || !isset($data['path']) || !is_string($data['path'])) {
                    continue;
                }

                $docPath = $data['path'];
                unset($data['path']); // Remove path from export data
                $this->exports[$docPath] = DocumentExports::fromArray($data);
                $loadedCount++;
            }
        }
    }

    /**
     * Validate shard directory name (2 lowercase hex characters).
     */
    private function isValidShardName(string $name): bool
    {
        if (strlen($name) !== 2) {
            return false;
        }

        return preg_match('/^[0-9a-f]{2}$/', $name) === 1;
    }

    /**
     * Save cache to output directory.
     *
     * Uses sharded storage for exports (each document in separate file).
     * Only writes changed exports for incremental efficiency.
     *
     * @param string $outputDir The output directory
     * @param string $settingsHash Hash of current settings for invalidation
     *
     * @throws RuntimeException If write operations fail
     */
    public function save(string $outputDir, string $settingsHash = ''): void
    {
        $this->outputDir = $outputDir;

        if (!is_dir($outputDir) && !mkdir($outputDir, 0o755, true)) {
            throw new RuntimeException('Failed to create cache directory: ' . $outputDir);
        }

        // Save sharded exports (only dirty ones)
        $this->saveShardedExports($outputDir);

        // Build main metadata file (no exports - they're sharded)
        $this->metadata = $this->versioning->createMetadata($settingsHash);

        $data = [
            'metadata' => $this->metadata,
            'dependencies' => $this->dependencyGraph->toArray(),
            'outputs' => $this->outputPaths,
        ];

        $metaPath = $outputDir . '/' . self::BUILD_META_FILE;
        $result = file_put_contents(
            $metaPath,
            json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
        );

        if ($result === false) {
            throw new RuntimeException('Failed to write cache metadata: ' . $metaPath);
        }

        // Clear dirty tracking after successful save
        $this->dirtyExports = [];
    }

    /**
     * Save exports to sharded directory structure.
     *
     * Directory structure: _exports/<hash-prefix>/<safe-filename>.json
     * Only writes files that have been modified (tracked in dirtyExports).
     */
    private function saveShardedExports(string $outputDir): void
    {
        $exportsDir = $outputDir . '/' . self::EXPORTS_DIR;

        // On first save or full rebuild, write all exports
        $writeAll = !is_dir($exportsDir) || $this->dirtyExports === [];

        if (!is_dir($exportsDir) && !mkdir($exportsDir, 0o755, true)) {
            throw new RuntimeException('Failed to create exports directory: ' . $exportsDir);
        }

        foreach ($this->exports as $docPath => $exports) {
            // Skip unchanged exports (incremental save)
            if (!$writeAll && !isset($this->dirtyExports[$docPath])) {
                continue;
            }

            $this->writeExportFile($exportsDir, $docPath, $exports);
        }
    }

    /**
     * Write a single export file to the sharded directory.
     */
    private function writeExportFile(string $exportsDir, string $docPath, DocumentExports $exports): void
    {
        // Use hash prefix for distribution (2 chars = 256 buckets)
        $hash = md5($docPath);
        $prefix = substr($hash, 0, 2);
        $shardDir = $exportsDir . '/' . $prefix;

        if (!is_dir($shardDir) && !mkdir($shardDir, 0o755, true)) {
            throw new RuntimeException('Failed to create shard directory: ' . $shardDir);
        }

        // Use hash as filename to handle special chars in doc paths
        $filename = $hash . '.json';
        $filePath = $shardDir . '/' . $filename;

        // Include path in the data for loading
        $data = $exports->toArray();
        $data['path'] = $docPath;

        $result = file_put_contents(
            $filePath,
            json_encode($data, JSON_THROW_ON_ERROR),
        );

        if ($result === false) {
            throw new RuntimeException('Failed to write export file: ' . $filePath);
        }
    }

    /**
     * Get the shard file path for a document.
     */
    private function getExportFilePath(string $outputDir, string $docPath): string
    {
        $hash = md5($docPath);
        $prefix = substr($hash, 0, 2);

        return $outputDir . '/' . self::EXPORTS_DIR . '/' . $prefix . '/' . $hash . '.json';
    }

    /**
     * Get exports for a document.
     */
    public function getExports(string $docPath): DocumentExports|null
    {
        return $this->exports[$docPath] ?? null;
    }

    /**
     * Set exports for a document.
     * Marks the export as dirty for incremental save.
     *
     * @throws InvalidArgumentException If maximum exports limit would be exceeded
     */
    public function setExports(string $docPath, DocumentExports $exports): void
    {
        if (!isset($this->exports[$docPath]) && count($this->exports) >= self::MAX_EXPORTS) {
            throw new InvalidArgumentException('exports exceed maximum of ' . self::MAX_EXPORTS);
        }

        $this->exports[$docPath] = $exports;
        $this->dirtyExports[$docPath] = true;
    }

    /**
     * Get all cached exports.
     *
     * @return array<string, DocumentExports>
     */
    public function getAllExports(): array
    {
        return $this->exports;
    }

    /**
     * Get all cached document paths.
     *
     * @return string[]
     */
    public function getAllDocPaths(): array
    {
        return array_keys($this->exports);
    }

    /**
     * Get the dependency graph.
     */
    public function getDependencyGraph(): DependencyGraph
    {
        return $this->dependencyGraph;
    }

    /**
     * Set output path for a document.
     *
     * @throws InvalidArgumentException If maximum output paths limit would be exceeded
     */
    public function setOutputPath(string $docPath, string $outputPath): void
    {
        if (!isset($this->outputPaths[$docPath]) && count($this->outputPaths) >= self::MAX_OUTPUT_PATHS) {
            throw new InvalidArgumentException('output paths exceed maximum of ' . self::MAX_OUTPUT_PATHS);
        }

        $this->outputPaths[$docPath] = $outputPath;
    }

    /**
     * Get output path for a document.
     */
    public function getOutputPath(string $docPath): string|null
    {
        return $this->outputPaths[$docPath] ?? null;
    }

    /**
     * Remove a document from all cache structures.
     * Also deletes the sharded export file if it exists.
     *
     * @throws RuntimeException If the export file cannot be deleted
     */
    public function removeDocument(string $docPath): void
    {
        unset($this->exports[$docPath]);
        unset($this->outputPaths[$docPath]);
        unset($this->dirtyExports[$docPath]);
        $this->dependencyGraph->removeDocument($docPath);

        // Delete sharded export file if output directory is known
        if ($this->outputDir === null) {
            return;
        }

        $exportFile = $this->getExportFilePath($this->outputDir, $docPath);
        if (!file_exists($exportFile)) {
            return;
        }

        if (!@unlink($exportFile)) {
            throw new RuntimeException('Failed to delete export file: ' . $exportFile);
        }
    }

    /**
     * Check if cache was loaded from disk.
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * Get cached settings hash.
     */
    public function getSettingsHash(): string|null
    {
        $hash = $this->metadata['settingsHash'] ?? null;

        return is_string($hash) ? $hash : null;
    }

    /**
     * Clear all cached data.
     */
    public function clear(): void
    {
        $this->exports = [];
        $this->dirtyExports = [];
        $this->dependencyGraph = new DependencyGraph();
        $this->outputPaths = [];
        $this->metadata = [];
        $this->loaded = false;
    }

    /**
     * Get cache statistics.
     *
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        return [
            'documents' => count($this->exports),
            'outputs' => count($this->outputPaths),
            'graph' => $this->dependencyGraph->getStats(),
            'loaded' => $this->loaded,
        ];
    }

    /**
     * Extract cache state for serialization (used in parallel compilation).
     *
     * @return array{exports: array<string, array<string, mixed>>, dependencies: array<string, mixed>, outputPaths: array<string, string>}
     */
    public function extractState(): array
    {
        $exportsData = [];
        foreach ($this->exports as $path => $exports) {
            $exportsData[$path] = $exports->toArray();
        }

        return [
            'exports' => $exportsData,
            'dependencies' => $this->dependencyGraph->toArray(),
            'outputPaths' => $this->outputPaths,
        ];
    }

    /**
     * Merge state from another cache instance (used after parallel compilation).
     *
     * @param array{exports?: array<string, array<string, mixed>>, dependencies?: array<string, mixed>, outputPaths?: array<string, string>} $state State from extractState()
     */
    public function mergeState(array $state): void
    {
        // Merge exports
        $exportsData = $state['exports'] ?? [];
        foreach ($exportsData as $path => $exportData) {
            if (!is_string($path) || !is_array($exportData)) {
                continue;
            }

            // Only add if not already present (first write wins)
            if (isset($this->exports[$path])) {
                continue;
            }

            if (count($this->exports) >= self::MAX_EXPORTS) {
                throw new InvalidArgumentException('exports exceed maximum during merge');
            }

            $this->exports[$path] = DocumentExports::fromArray($exportData);
        }

        // Merge dependency graph
        $depsData = $state['dependencies'] ?? [];
        if (is_array($depsData) && $depsData !== []) {
            $childGraph = DependencyGraph::fromArray($depsData);
            $this->dependencyGraph->merge($childGraph);
        }

        // Merge output paths
        $outputPaths = $state['outputPaths'] ?? [];
        foreach ($outputPaths as $docPath => $outputPath) {
            if (!is_string($docPath) || !is_string($outputPath)) {
                continue;
            }

            if (isset($this->outputPaths[$docPath])) {
                continue;
            }

            if (count($this->outputPaths) >= self::MAX_OUTPUT_PATHS) {
                throw new InvalidArgumentException('output paths exceed maximum during merge');
            }

            $this->outputPaths[$docPath] = $outputPath;
        }
    }

    /**
     * Set the input directory for file path resolution.
     */
    public function setInputDir(string $inputDir): void
    {
        $this->inputDir = $inputDir;
    }

    /**
     * Get the input directory for file path resolution.
     */
    public function getInputDir(): string
    {
        return $this->inputDir;
    }
}
