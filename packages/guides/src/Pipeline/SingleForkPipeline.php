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

namespace phpDocumentor\Guides\Pipeline;

use phpDocumentor\Guides\Build\Parallel\CpuDetector;
use phpDocumentor\Guides\Build\Parallel\ParallelSettings;
use phpDocumentor\Guides\Build\Parallel\ProcessManager;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RuntimeException;
use SplFileInfo;
use Throwable;

use function array_chunk;
use function array_flip;
use function array_map;
use function array_merge;
use function assert;
use function ceil;
use function count;
use function file_get_contents;
use function file_put_contents;
use function function_exists;
use function fwrite;
use function implode;
use function in_array;
use function is_array;
use function is_dir;
use function is_string;
use function max;
use function min;
use function pcntl_fork;
use function preg_replace_callback;
use function serialize;
use function sprintf;
use function strpos;
use function unserialize;

use const STDERR;

/**
 * Single-fork pipeline: fork once, each worker does parse → compile → render.
 *
 * This is the simplest parallel architecture:
 * 1. Quick scan to discover files and toctree order (sequential, fast)
 * 2. Fork workers - each does full pipeline for its batch (parallel, CPU-heavy)
 * 3. Post-process HTML to resolve navigation placeholders (sequential, fast)
 *
 * Benefits over multi-phase approach:
 * - Single fork/wait cycle instead of multiple
 * - No inter-process serialization of AST/ProjectNode
 * - Each worker is independent, no merge step
 * - Simpler code, fewer failure modes
 */
final class SingleForkPipeline
{
    /** Minimum files before parallelization is worthwhile */
    private const MIN_FILES_FOR_PARALLEL = 10;

    /**
     * Placeholder pattern for navigation links.
     *
     * Uses pipe (|) as delimiter instead of colon (:) to support paths containing colons
     * (Windows drive letters, URLs, etc.). Format: <!--GUIDES_NAV|prev|/path/to/doc|placeholder-->
     *
     * @see https://regex101.com/r/9IjvEa/1
     */
    private const NAV_PLACEHOLDER_REGEX = '/<!--GUIDES_NAV\|(prev|next)\|([^|]+)\|([^>]+)-->/';

    private int $workerCount;

    public function __construct(
        private readonly LoggerInterface|null $logger = null,
        int|null $workerCount = null,
    ) {
        $this->workerCount = $workerCount ?? $this->detectCpuCount();
    }

    /**
     * Execute the full pipeline with optional parallelization.
     *
     * @param callable(string[]): array{documents: DocumentNode[], projectNode: ProjectNode} $pipelineExecutor
     *        Function that executes parse→compile→render for a batch of files
     * @param string[] $allFiles All files to process
     * @param string $outputDir Output directory for rendered HTML
     *
     * @return array{documents: DocumentNode[], projectNode: ProjectNode}
     */
    public function execute(
        callable $pipelineExecutor,
        array $allFiles,
        string $outputDir,
    ): array {
        // Check if parallel is worthwhile
        if (!$this->shouldFork(count($allFiles))) {
            $this->logger?->debug('Using sequential pipeline');

            return $pipelineExecutor($allFiles);
        }

        $this->logger?->info(sprintf(
            'Starting single-fork pipeline: %d files across %d workers',
            count($allFiles),
            $this->workerCount,
        ));

        // Partition files into batches
        $batchSize = (int) ceil(count($allFiles) / $this->workerCount);
        $batches = array_chunk($allFiles, max(1, $batchSize));

        // Create temp files for results
        $tempFiles = [];
        $childPids = [];

        foreach ($batches as $workerId => $batch) {
            if ($batch === []) {
                continue;
            }

            $tempFile = ProcessManager::createSecureTempFile('pipeline_' . $workerId . '_');
            if ($tempFile === false) {
                $this->logger?->error('Failed to create temp file, falling back to sequential');

                return $pipelineExecutor($allFiles);
            }

            $tempFiles[$workerId] = $tempFile;

            $pid = pcntl_fork();

            if ($pid === -1) {
                $this->logger?->error('pcntl_fork failed, falling back to sequential');
                foreach ($tempFiles as $tf) {
                    ProcessManager::cleanupTempFile($tf);
                }

                return $pipelineExecutor($allFiles);
            }

            if ($pid === 0) {
                // Child: clear inherited temp file tracking
                ProcessManager::clearTempFileTracking();
                try {
                    $result = $pipelineExecutor($batch);
                    // Only serialize document paths (not full AST) to save memory
                    $paths = array_map(
                        static fn (DocumentNode $doc) => $doc->getFilePath(),
                        $result['documents'],
                    );

                    if (file_put_contents($tempFile, serialize(['paths' => $paths])) === false) {
                        fwrite(STDERR, '[Worker ' . $workerId . '] Failed to write results to temp file' . "\n");
                        exit(1);
                    }
                } catch (Throwable $e) {
                    fwrite(STDERR, sprintf(
                        "[Worker %d] Pipeline failed: %s\n",
                        $workerId,
                        $e->getMessage(),
                    ));
                    // Best effort to write error - if this fails too, exit with error code
                    if (file_put_contents($tempFile, serialize(['error' => $e->getMessage()])) === false) {
                        exit(1);
                    }
                }

                exit(0);
            }

            // Parent: record child PID
            $childPids[$workerId] = $pid;
        }

        // Wait for all children with timeout
        $waitResult = ProcessManager::waitForChildrenWithTimeout($childPids);
        $allPaths = [];
        $failures = [];

        foreach ($childPids as $workerId => $pid) {
            // Only read results from successful workers
            if (in_array($workerId, $waitResult['successes'], true)) {
                $serialized = file_get_contents($tempFiles[$workerId]);
                if ($serialized !== false && $serialized !== '') {
                    $data = unserialize($serialized);
                    if (is_array($data) && isset($data['paths']) && is_array($data['paths'])) {
                        /** @var string[] $paths */
                        $paths = $data['paths'];
                        $allPaths = array_merge($allPaths, $paths);
                    }

                    if (is_array($data) && isset($data['error']) && is_string($data['error'])) {
                        $failures[$workerId] = $data['error'];
                    }
                }
            } else {
                $reason = $waitResult['failures'][$workerId] ?? 'unknown';
                $failures[$workerId] = $reason;
            }

            ProcessManager::cleanupTempFile($tempFiles[$workerId]);
        }

        // Fail fast on worker failures to prevent incomplete documentation
        if ($failures !== []) {
            $errorDetails = [];
            foreach ($failures as $workerId => $reason) {
                $errorDetails[] = sprintf('Worker %d: %s', $workerId, $reason);
                $this->logger?->error(sprintf('Pipeline worker %d failed: %s', $workerId, $reason));
            }

            throw new RuntimeException(
                'Single-fork pipeline failed: ' . implode(', ', $errorDetails),
            );
        }

        // Post-process: resolve navigation placeholders
        $this->resolveNavigationPlaceholders($outputDir, $allPaths);

        $this->logger?->info(sprintf(
            'Single-fork pipeline complete: %d documents processed',
            count($allPaths),
        ));

        // Return empty result since documents were rendered by children
        return ['documents' => [], 'projectNode' => new ProjectNode()];
    }

    /**
     * Post-process HTML files to resolve navigation placeholders.
     *
     * Placeholders format: <!--GUIDES_NAV|type|currentPath|targetPath-->
     * After all rendering is complete, we know the full document order and can resolve these.
     *
     * @param string[] $documentPaths
     */
    private function resolveNavigationPlaceholders(string $outputDir, array $documentPaths): void
    {
        // Build path -> index map for quick lookup
        $pathIndex = array_flip($documentPaths);

        // Scan all HTML files using RecursiveDirectoryIterator (portable, works everywhere)
        $htmlFiles = $this->findHtmlFiles($outputDir);

        foreach ($htmlFiles as $htmlFile) {
            $content = file_get_contents($htmlFile);
            if ($content === false) {
                continue;
            }

            // Check if file has placeholders
            if (strpos($content, '<!--GUIDES_NAV|') === false) {
                continue;
            }

            // Replace placeholders
            $newContent = preg_replace_callback(
                self::NAV_PLACEHOLDER_REGEX,
                static function (array $matches) use ($pathIndex, $documentPaths): string {
                    $type = $matches[1]; // 'prev' or 'next'
                    $currentPath = $matches[2];

                    if (!isset($pathIndex[$currentPath])) {
                        return ''; // Unknown document
                    }

                    $currentIndex = $pathIndex[$currentPath];
                    $targetIndex = $type === 'prev' ? $currentIndex - 1 : $currentIndex + 1;

                    if ($targetIndex < 0 || $targetIndex >= count($documentPaths)) {
                        return ''; // No prev/next
                    }

                    // Return the target path - actual HTML generation is done by Twig
                    return $documentPaths[$targetIndex];
                },
                $content,
            );

            if ($newContent === null || $newContent === $content) {
                continue;
            }

            file_put_contents($htmlFile, $newContent);
        }
    }

    private function shouldFork(int $fileCount): bool
    {
        if (!function_exists('pcntl_fork')) {
            return false;
        }

        if ($fileCount < self::MIN_FILES_FOR_PARALLEL) {
            return false;
        }

        return $this->workerCount >= 2;
    }

    private function detectCpuCount(): int
    {
        return CpuDetector::detectCores();
    }

    public function setWorkerCount(int $count): void
    {
        $this->workerCount = max(1, min($count, ParallelSettings::MAX_WORKERS));
    }

    /**
     * Find all HTML files in a directory recursively.
     *
     * Uses RecursiveDirectoryIterator which is more portable than glob() with **
     * and works consistently across all PHP installations and operating systems.
     *
     * @return list<string> Paths to HTML files
     */
    private function findHtmlFiles(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $htmlFiles = [];
        $directoryIterator = new RecursiveDirectoryIterator(
            $directory,
            RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS,
        );
        $iterator = new RecursiveIteratorIterator(
            $directoryIterator,
            RecursiveIteratorIterator::LEAVES_ONLY,
        );
        $regexIterator = new RegexIterator($iterator, '/\.html$/i');

        foreach ($regexIterator as $file) {
            assert($file instanceof SplFileInfo);
            $htmlFiles[] = $file->getPathname();
        }

        return $htmlFiles;
    }
}
