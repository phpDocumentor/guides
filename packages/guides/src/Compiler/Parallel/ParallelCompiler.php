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

namespace phpDocumentor\Guides\Compiler\Parallel;

use phpDocumentor\Guides\Build\Parallel\CpuDetector;
use phpDocumentor\Guides\Build\Parallel\ProcessManager;
use phpDocumentor\Guides\Compiler\Compiler;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Compiler\DocumentNodeTraverser;
use phpDocumentor\Guides\Compiler\NodeTransformers\NodeTransformerFactory;
use phpDocumentor\Guides\Compiler\NodeTransformers\TransformerPass;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\ExternalEntryNode;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SplPriorityQueue;
use Traversable;

use function array_chunk;
use function ceil;
use function count;
use function file_get_contents;
use function file_put_contents;
use function function_exists;
use function fwrite;
use function implode;
use function in_array;
use function is_array;
use function iterator_to_array;
use function max;
use function pcntl_fork;
use function serialize;
use function sprintf;
use function unserialize;

use const STDERR;

/**
 * Parallel compiler that uses fork-based parallelization for compilation phases.
 *
 * The compilation process is split into phases based on shared state dependencies:
 *
 * Phase 1 - Collection (parallel): priority >= 4900
 *   - DocumentEntryRegistrationTransformer, CollectLinkTargetsTransformer, etc.
 *   - These WRITE to ProjectNode but don't READ cross-document data
 *   - Each child collects metadata to DocumentCompilationResult
 *
 * Phase 2 - Merge (sequential): fast O(n)
 *   - Merge all DocumentCompilationResults into ProjectNode
 *
 * Phase 3 - Resolution (parallel): priority 4500-1000
 *   - Menu resolvers, citation resolvers, etc.
 *   - These READ from ProjectNode (now complete) and WRITE to documents
 *
 * Phase 4 - Finalization (sequential): priority < 1000
 *   - AutomaticMenuPass, GlobalMenuPass, ToctreeValidationPass
 *   - These do cross-document mutations
 */
final class ParallelCompiler
{
    /** Minimum document count before parallelization is worthwhile */
    private const MIN_DOCS_FOR_PARALLEL = 10;

    /** Priority threshold for collection phase */
    private const COLLECTION_PRIORITY_MIN = 4900;

    /** Priority threshold for resolution phase */
    private const RESOLUTION_PRIORITY_MIN = 1000;
    private const RESOLUTION_PRIORITY_MAX = 4500;

    /** @var SplPriorityQueue<int, CompilerPass> */
    private readonly SplPriorityQueue $collectionPasses;

    /** @var SplPriorityQueue<int, CompilerPass> */
    private readonly SplPriorityQueue $resolutionPasses;

    /** @var SplPriorityQueue<int, CompilerPass> */
    private readonly SplPriorityQueue $finalizationPasses;

    private readonly int $workerCount;
    private bool $parallelEnabled = true;

    /** @param iterable<CompilerPass> $passes */
    public function __construct(
        private readonly Compiler $sequentialCompiler,
        iterable $passes,
        NodeTransformerFactory $nodeTransformerFactory,
        private readonly CompilationCacheInterface|null $compilationCache = null,
        private readonly LoggerInterface|null $logger = null,
        int|null $workerCount = null,
    ) {
        $this->collectionPasses = new SplPriorityQueue();
        $this->resolutionPasses = new SplPriorityQueue();
        $this->finalizationPasses = new SplPriorityQueue();
        $this->workerCount = $workerCount ?? $this->detectCpuCount();

        // Convert to array to allow multiple iterations
        $passesArray = $passes instanceof Traversable ? iterator_to_array($passes) : (array) $passes;

        // Categorize compiler passes for parallel execution
        foreach ($passesArray as $pass) {
            $this->categorizePass($pass);
        }

        // Categorize transformer passes
        $transformerPriorities = $nodeTransformerFactory->getPriorities();
        foreach ($transformerPriorities as $priority) {
            $pass = new TransformerPass(
                new DocumentNodeTraverser($nodeTransformerFactory, $priority),
                $priority,
            );
            $this->categorizePass($pass);
        }
    }

    private function categorizePass(CompilerPass $pass): void
    {
        $priority = $pass->getPriority();

        if ($priority >= self::COLLECTION_PRIORITY_MIN) {
            $this->collectionPasses->insert($pass, $priority);
        } elseif ($priority >= self::RESOLUTION_PRIORITY_MIN && $priority <= self::RESOLUTION_PRIORITY_MAX) {
            $this->resolutionPasses->insert($pass, $priority);
        } else {
            $this->finalizationPasses->insert($pass, $priority);
        }
    }

    /**
     * @param DocumentNode[] $documents
     *
     * @return DocumentNode[]
     */
    public function run(array $documents, CompilerContext $compilerContext): array
    {
        $documentCount = count($documents);

        if (!$this->shouldFork($documentCount)) {
            $this->logger?->debug(sprintf(
                'Using sequential compilation: %d documents (parallel=%s, pcntl=%s)',
                $documentCount,
                $this->parallelEnabled ? 'enabled' : 'disabled',
                function_exists('pcntl_fork') ? 'available' : 'unavailable',
            ));

            return $this->runSequentially($documents, $compilerContext);
        }

        $this->logger?->info(sprintf(
            'Starting parallel compilation: %d documents across %d workers',
            $documentCount,
            $this->workerCount,
        ));

        // Phase 1: Parallel Collection
        $this->logger?->debug('Phase 1: Parallel collection');
        [$documents, $results] = $this->runCollectionPhase($documents, $compilerContext);

        // Phase 2: Sequential Merge (including toctree relationships)
        $this->logger?->debug('Phase 2: Sequential merge');
        $mergedRelationships = $this->runMergePhase($results, $compilerContext);

        // Phase 2.5: Fix document entry references
        // After serialization, documents have different DocumentEntryNode instances than ProjectNode.
        // We must fix this BEFORE resolution phase so transformers work with correct entries.
        $this->logger?->debug('Phase 2.5: Fixing document entry references');
        $documents = $this->fixDocumentEntryReferences($documents, $compilerContext);

        // Phase 2.6: Resolve toctree relationships
        // Convert path-based relationships back to object references on ProjectNode's entries
        $this->logger?->debug('Phase 2.6: Resolving toctree relationships');
        $this->resolveDocumentRelationships($mergedRelationships, $compilerContext);

        // Phase 3: Parallel Resolution
        $this->logger?->debug('Phase 3: Parallel resolution');
        $documents = $this->runResolutionPhase($documents, $compilerContext);

        // Phase 3.5: Fix document entry references again
        // Resolution phase serializes documents, breaking references. Fix them again.
        $this->logger?->debug('Phase 3.5: Fixing document entry references post-resolution');
        $documents = $this->fixDocumentEntryReferences($documents, $compilerContext);

        // Phase 4: Sequential Finalization
        $this->logger?->debug('Phase 4: Sequential finalization');
        $documents = $this->runFinalizationPhase($documents, $compilerContext);

        $this->logger?->info('Parallel compilation complete');

        return $documents;
    }

    /**
     * Run all passes sequentially using the original Compiler.
     *
     * This ensures exact compatibility with the standard compilation behavior.
     *
     * @param DocumentNode[] $documents
     *
     * @return DocumentNode[]
     */
    private function runSequentially(array $documents, CompilerContext $compilerContext): array
    {
        return $this->sequentialCompiler->run($documents, $compilerContext);
    }

    /**
     * Phase 1: Run collection transformers in parallel.
     *
     * @param DocumentNode[] $documents
     *
     * @return array{0: DocumentNode[], 1: DocumentCompilationResult[]}
     */
    private function runCollectionPhase(array $documents, CompilerContext $compilerContext): array
    {
        // Partition documents into batches
        $batches = $this->partitionDocuments($documents, $this->workerCount);

        $tempFiles = [];
        $childPids = [];

        foreach ($batches as $workerId => $batch) {
            if ($batch === []) {
                continue;
            }

            $tempFile = ProcessManager::createSecureTempFile('compile_collect_' . $workerId . '_');
            if ($tempFile === false) {
                $this->logger?->error('Failed to create temp file, falling back to sequential');

                return [$this->runSequentially($documents, $compilerContext), []];
            }

            $tempFiles[$workerId] = $tempFile;

            $pid = pcntl_fork();

            if ($pid === -1) {
                $this->logger?->error('pcntl_fork failed, falling back to sequential');
                foreach ($tempFiles as $tf) {
                    ProcessManager::cleanupTempFile($tf);
                }

                return [$this->runSequentially($documents, $compilerContext), []];
            }

            if ($pid === 0) {
                // Child process: clear inherited temp file tracking
                ProcessManager::clearTempFileTracking();
                $this->processCollectionBatch($batch, $compilerContext, $tempFile);
                exit(0);
            }

            $childPids[$workerId] = $pid;
        }

        // Wait for children with timeout and collect results
        $waitResult = ProcessManager::waitForChildrenWithTimeout($childPids);
        $allDocuments = [];
        $allResults = [];

        foreach ($childPids as $workerId => $pid) {
            // Only read results from successful workers
            if (in_array($workerId, $waitResult['successes'], true)) {
                $serialized = file_get_contents($tempFiles[$workerId]);
                if ($serialized !== false && $serialized !== '') {
                    $data = unserialize($serialized);
                    if (is_array($data) && isset($data['documents'], $data['result'])) {
                        /** @var array<mixed> $batchDocuments */
                        $batchDocuments = $data['documents'];
                        foreach ($batchDocuments as $doc) {
                            if (!($doc instanceof DocumentNode)) {
                                continue;
                            }

                            $allDocuments[$doc->getFilePath()] = $doc;
                        }

                        if ($data['result'] instanceof DocumentCompilationResult) {
                            $allResults[] = $data['result'];
                        }
                    }
                }
            }

            ProcessManager::cleanupTempFile($tempFiles[$workerId]);
        }

        // Fail fast on worker failures to prevent incomplete ProjectNode
        if ($waitResult['failures'] !== []) {
            $errorDetails = [];
            foreach ($waitResult['failures'] as $workerId => $reason) {
                $errorDetails[] = sprintf('Worker %d: %s', $workerId, $reason);
                $this->logger?->error(sprintf('Collection worker %d failed: %s', $workerId, $reason));
            }

            throw new RuntimeException(
                'Parallel collection failed: ' . implode(', ', $errorDetails),
            );
        }

        // Preserve document order
        $orderedDocuments = [];
        foreach ($documents as $doc) {
            if (!isset($allDocuments[$doc->getFilePath()])) {
                continue;
            }

            $orderedDocuments[] = $allDocuments[$doc->getFilePath()];
        }

        return [$orderedDocuments, $allResults];
    }

    /**
     * Process a batch of documents in child process for collection phase.
     *
     * @param DocumentNode[] $batch
     */
    private function processCollectionBatch(
        array $batch,
        CompilerContext $compilerContext,
        string $tempFile,
    ): void {
        // Run collection passes on this batch
        // These transformers will write to the child's copy of ProjectNode
        $passes = clone $this->collectionPasses;
        foreach ($passes as $pass) {
            $batch = $pass->run($batch, $compilerContext);
        }

        // Extract all data that was added to ProjectNode during collection
        // This captures document entries, link targets, citations, etc.
        $result = DocumentCompilationResult::extractFromProjectNode(
            $compilerContext->getProjectNode(),
        );

        // Serialize documents and extracted result
        $serialized = serialize([
            'documents' => $batch,
            'result' => $result,
        ]);

        if (file_put_contents($tempFile, $serialized) === false) {
            fwrite(STDERR, "Failed to write collection results to temp file\n");
            exit(1);
        }
    }

    /**
     * Phase 2: Merge all collected data into ProjectNode.
     *
     * This is O(n) where n = total entries across all results.
     * Uses hash-based deduplication for O(1) duplicate checks.
     *
     * @param DocumentCompilationResult[] $results
     *
     * @return array<string, array{children: list<array{type: string, path?: string, url?: string, title?: string}>, parent: string|null}>
     */
    private function runMergePhase(array $results, CompilerContext $compilerContext): array
    {
        $projectNode = $compilerContext->getProjectNode();

        // Merge toctree relationships from all batches
        // Use separate tracking for seen children per path for O(1) deduplication
        /** @var array<string, array{children: list<array{type: string, path?: string, url?: string, title?: string}>, parent: string|null}> $allRelationships */
        $allRelationships = [];
        /** @var array<string, array<string, true>> $seenChildren path -> [childKey => true] */
        $seenChildren = [];

        foreach ($results as $result) {
            $result->mergeIntoProjectNode($projectNode);

            // Merge toctree relationships
            foreach ($result->toctreeRelationships as $path => $relations) {
                if (!isset($allRelationships[$path])) {
                    $allRelationships[$path] = ['children' => [], 'parent' => null];
                    $seenChildren[$path] = [];
                }

                // Merge children using hash-based deduplication (O(1) per child)
                foreach ($relations['children'] as $child) {
                    // Generate unique key for this child
                    $childKey = $this->getChildKey($child);

                    // O(1) duplicate check using isset
                    if (isset($seenChildren[$path][$childKey])) {
                        continue;
                    }

                    $seenChildren[$path][$childKey] = true;
                    $allRelationships[$path]['children'][] = $child;
                }

                // Take non-null parent (should be consistent across batches)
                if ($relations['parent'] === null) {
                    continue;
                }

                $allRelationships[$path]['parent'] = $relations['parent'];
            }
        }

        $this->logger?->debug(sprintf(
            'Merged %d results: %d document entries, %d link target types, %d toctree relationships',
            count($results),
            count($projectNode->getAllDocumentEntries()),
            count($projectNode->getAllInternalTargets()),
            count($allRelationships),
        ));

        return $allRelationships;
    }

    /**
     * Generate a unique key for a toctree child entry.
     *
     * @param array{type: string, path?: string, url?: string, title?: string} $child
     */
    private function getChildKey(array $child): string
    {
        return $child['type'] . ':' . ($child['path'] ?? $child['url'] ?? '');
    }

    /**
     * Resolve path-based toctree relationships to actual object references.
     *
     * During parallel compilation, relationships are stored as path strings to survive
     * serialization. This method reconstructs the object graph by looking up paths
     * in ProjectNode's document entries.
     *
     * @param array<string, array{children: list<array{type: string, path?: string, url?: string, title?: string}>, parent: string|null}> $relationships
     */
    private function resolveDocumentRelationships(
        array $relationships,
        CompilerContext $compilerContext,
    ): void {
        $projectNode = $compilerContext->getProjectNode();
        $allEntries = $projectNode->getAllDocumentEntries();

        // Build path => DocumentEntryNode lookup for O(1) resolution
        $entriesByPath = [];
        foreach ($allEntries as $entry) {
            $entriesByPath[$entry->getFile()] = $entry;
        }

        $resolvedCount = 0;
        $externalCount = 0;

        // Resolve relationships for each document entry
        foreach ($relationships as $path => $relations) {
            $entry = $entriesByPath[$path] ?? null;
            if ($entry === null) {
                continue;
            }

            // Clear existing children (they have broken object refs from serialization)
            $entry->setMenuEntries([]);

            // Resolve and add children
            foreach ($relations['children'] as $childData) {
                if ($childData['type'] === 'document') {
                    $childPath = $childData['path'] ?? '';
                    $childEntry = $entriesByPath[$childPath] ?? null;
                    if ($childEntry !== null) {
                        $entry->addChild($childEntry);
                        $resolvedCount++;
                    }
                } elseif ($childData['type'] === 'external') {
                    // Reconstruct ExternalEntryNode
                    $url = $childData['url'] ?? '';
                    $title = $childData['title'] ?? '';
                    $externalEntry = new ExternalEntryNode($url, $title);
                    $entry->addChild($externalEntry);
                    $externalCount++;
                }
            }

            // Resolve and set parent
            if ($relations['parent'] === null) {
                continue;
            }

            $parentEntry = $entriesByPath[$relations['parent']] ?? null;
            $entry->setParent($parentEntry);
        }

        $this->logger?->debug(sprintf(
            'Resolved %d document relationships, %d external entries',
            $resolvedCount,
            $externalCount,
        ));
    }

    /**
     * Phase 3: Run resolution transformers in parallel.
     *
     * @param DocumentNode[] $documents
     *
     * @return DocumentNode[]
     */
    private function runResolutionPhase(array $documents, CompilerContext $compilerContext): array
    {
        if (count(clone $this->resolutionPasses) === 0) {
            return $documents;
        }

        $batches = $this->partitionDocuments($documents, $this->workerCount);
        $tempFiles = [];
        $childPids = [];

        foreach ($batches as $workerId => $batch) {
            if ($batch === []) {
                continue;
            }

            $tempFile = ProcessManager::createSecureTempFile('compile_resolve_' . $workerId . '_');
            if ($tempFile === false) {
                return $this->runResolutionSequentially($documents, $compilerContext);
            }

            $tempFiles[$workerId] = $tempFile;

            $pid = pcntl_fork();

            if ($pid === -1) {
                foreach ($tempFiles as $tf) {
                    ProcessManager::cleanupTempFile($tf);
                }

                return $this->runResolutionSequentially($documents, $compilerContext);
            }

            if ($pid === 0) {
                // Child process: clear inherited temp file tracking
                ProcessManager::clearTempFileTracking();
                $this->processResolutionBatch($batch, $compilerContext, $tempFile);
                exit(0);
            }

            $childPids[$workerId] = $pid;
        }

        // Wait for children with timeout and collect results
        $waitResult = ProcessManager::waitForChildrenWithTimeout($childPids);
        $allDocuments = [];
        $cacheStates = [];

        foreach ($childPids as $workerId => $pid) {
            // Only read results from successful workers
            if (in_array($workerId, $waitResult['successes'], true)) {
                $serialized = file_get_contents($tempFiles[$workerId]);
                if ($serialized !== false && $serialized !== '') {
                    $data = unserialize($serialized);
                    if (is_array($data)) {
                        // New format with cache state
                        if (isset($data['documents']) && is_array($data['documents'])) {
                            foreach ($data['documents'] as $doc) {
                                if (!($doc instanceof DocumentNode)) {
                                    continue;
                                }

                                $allDocuments[$doc->getFilePath()] = $doc;
                            }

                            // Collect cache state for merging
                            if (isset($data['cacheState']) && is_array($data['cacheState'])) {
                                /** @var array{exports?: array<string, array<string, mixed>>, dependencies?: array<string, mixed>, outputPaths?: array<string, string>} $cacheState */
                                $cacheState = $data['cacheState'];
                                $cacheStates[] = $cacheState;
                            }
                        } else {
                            // Legacy format (just documents array)
                            foreach ($data as $doc) {
                                if (!($doc instanceof DocumentNode)) {
                                    continue;
                                }

                                $allDocuments[$doc->getFilePath()] = $doc;
                            }
                        }
                    }
                }
            }

            ProcessManager::cleanupTempFile($tempFiles[$workerId]);
        }

        // Fail fast on worker failures to prevent incomplete compilation
        if ($waitResult['failures'] !== []) {
            $errorDetails = [];
            foreach ($waitResult['failures'] as $workerId => $reason) {
                $errorDetails[] = sprintf('Worker %d: %s', $workerId, $reason);
                $this->logger?->error(sprintf('Resolution worker %d failed: %s', $workerId, $reason));
            }

            throw new RuntimeException(
                'Parallel resolution failed: ' . implode(', ', $errorDetails),
            );
        }

        // Merge cache states from all children
        if ($this->compilationCache !== null && $cacheStates !== []) {
            foreach ($cacheStates as $state) {
                $this->compilationCache->mergeState($state);
            }

            $this->logger?->debug(sprintf(
                'Merged cache states from %d workers, now have %d exports',
                count($cacheStates),
                count($this->compilationCache->getAllExports()),
            ));
        }

        // Preserve order
        $orderedDocuments = [];
        foreach ($documents as $doc) {
            if (!isset($allDocuments[$doc->getFilePath()])) {
                continue;
            }

            $orderedDocuments[] = $allDocuments[$doc->getFilePath()];
        }

        return $orderedDocuments;
    }

    /** @param DocumentNode[] $batch */
    private function processResolutionBatch(
        array $batch,
        CompilerContext $compilerContext,
        string $tempFile,
    ): void {
        $passes = clone $this->resolutionPasses;
        foreach ($passes as $pass) {
            $batch = $pass->run($batch, $compilerContext);
        }

        // Serialize documents and cache state (if cache is available)
        $data = [
            'documents' => $batch,
            'cacheState' => $this->compilationCache?->extractState() ?? [],
        ];

        if (file_put_contents($tempFile, serialize($data)) === false) {
            fwrite(STDERR, "Failed to write resolution results to temp file\n");
            exit(1);
        }
    }

    /**
     * @param DocumentNode[] $documents
     *
     * @return DocumentNode[]
     */
    private function runResolutionSequentially(array $documents, CompilerContext $compilerContext): array
    {
        $passes = clone $this->resolutionPasses;
        foreach ($passes as $pass) {
            $documents = $pass->run($documents, $compilerContext);
        }

        return $documents;
    }

    /**
     * Phase 4: Run finalization passes sequentially.
     *
     * @param DocumentNode[] $documents
     *
     * @return DocumentNode[]
     */
    private function runFinalizationPhase(array $documents, CompilerContext $compilerContext): array
    {
        $passes = clone $this->finalizationPasses;
        foreach ($passes as $pass) {
            $documents = $pass->run($documents, $compilerContext);
        }

        return $documents;
    }

    /**
     * Fix document entry references after parallel processing.
     *
     * After serialization/unserialization in child processes, DocumentNode objects
     * have different DocumentEntryNode instances than those stored in ProjectNode.
     * The renderer uses identity comparison (===) to match documents with entries,
     * so we need to restore object identity by setting the ProjectNode's entries
     * on each document.
     *
     * Additionally, during resolution phase, transformers may have added children
     * to the unserialized entries. We must transfer those children to ProjectNode's
     * entries before replacing the references.
     *
     * @param DocumentNode[] $documents
     *
     * @return DocumentNode[]
     */
    private function fixDocumentEntryReferences(array $documents, CompilerContext $compilerContext): array
    {
        $projectNode = $compilerContext->getProjectNode();
        $projectEntries = $projectNode->getAllDocumentEntries();

        // Build a lookup map by file path
        $entriesByPath = [];
        foreach ($projectEntries as $entry) {
            $entriesByPath[$entry->getFile()] = $entry;
        }

        // Pre-build existing children sets for O(1) duplicate detection
        /** @var array<string, array<string, true>> $existingChildrenByEntry path -> [childKey => true] */
        $existingChildrenByEntry = [];
        foreach ($projectEntries as $entry) {
            $entryPath = $entry->getFile();
            $existingChildrenByEntry[$entryPath] = [];
            foreach ($entry->getMenuEntries() as $child) {
                if ($child instanceof DocumentEntryNode) {
                    $existingChildrenByEntry[$entryPath]['doc:' . $child->getFile()] = true;
                } elseif ($child instanceof ExternalEntryNode) {
                    $existingChildrenByEntry[$entryPath]['ext:' . $child->getValue()] = true;
                }
            }
        }

        // Update each document to use the ProjectNode's entry instance
        foreach ($documents as $document) {
            $filePath = $document->getFilePath();
            if (!isset($entriesByPath[$filePath])) {
                continue;
            }

            $projectEntry = $entriesByPath[$filePath];
            $documentEntry = $document->getDocumentEntry();

            // Transfer children from unserialized entry to ProjectNode's entry
            // (children may have been added during resolution phase)
            if ($documentEntry !== null && $documentEntry !== $projectEntry) {
                foreach ($documentEntry->getMenuEntries() as $child) {
                    // Resolve child to ProjectNode's entry if it's a document
                    if ($child instanceof DocumentEntryNode) {
                        $childPath = $child->getFile();
                        $childKey = 'doc:' . $childPath;
                        $resolvedChild = $entriesByPath[$childPath] ?? null;

                        // O(1) duplicate check using isset
                        if ($resolvedChild !== null && !isset($existingChildrenByEntry[$filePath][$childKey])) {
                            $projectEntry->addChild($resolvedChild);
                            $resolvedChild->setParent($projectEntry);
                            $existingChildrenByEntry[$filePath][$childKey] = true;
                        }
                    } elseif ($child instanceof ExternalEntryNode) {
                        $childKey = 'ext:' . $child->getValue();

                        // O(1) duplicate check using isset
                        if (!isset($existingChildrenByEntry[$filePath][$childKey])) {
                            $projectEntry->addChild($child);
                            $existingChildrenByEntry[$filePath][$childKey] = true;
                        }
                    }
                }

                // Transfer parent if set and not already set on ProjectNode's entry
                $parent = $documentEntry->getParent();
                if ($parent !== null && $projectEntry->getParent() === null) {
                    $parentPath = $parent->getFile();
                    $resolvedParent = $entriesByPath[$parentPath] ?? null;
                    if ($resolvedParent !== null) {
                        $projectEntry->setParent($resolvedParent);
                    }
                }
            }

            $document->setDocumentEntry($projectEntry);
        }

        return $documents;
    }

    /**
     * @param DocumentNode[] $documents
     *
     * @return array<int, DocumentNode[]>
     */
    private function partitionDocuments(array $documents, int $workerCount): array
    {
        $batchSize = (int) ceil(count($documents) / $workerCount);

        return array_chunk($documents, max(1, $batchSize));
    }

    private function shouldFork(int $documentCount): bool
    {
        if (!$this->parallelEnabled) {
            return false;
        }

        if (!function_exists('pcntl_fork')) {
            return false;
        }

        if ($documentCount < self::MIN_DOCS_FOR_PARALLEL) {
            return false;
        }

        return $this->workerCount >= 2;
    }

    private function detectCpuCount(): int
    {
        return CpuDetector::detectCores();
    }

    public function setParallelEnabled(bool $enabled): void
    {
        $this->parallelEnabled = $enabled;
    }

    public function isParallelEnabled(): bool
    {
        return $this->parallelEnabled;
    }
}
