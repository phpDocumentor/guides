===================
Parallel Processing
===================

The guides library provides infrastructure for parallel processing using PHP's
``pcntl_fork()``. This enables applications to utilize multiple CPU cores for
parsing, compiling, and rendering documentation.

.. note::

    Parallel processing requires the ``pcntl`` PHP extension, which is only
    available on Linux and macOS. Windows users should use sequential processing.

    For user-facing documentation, see :doc:`/cli/parallel-processing`.

Architecture Overview
=====================

Parallel processing support consists of several layers:

**Core Utilities** (``Build\Parallel``):

- ``CpuDetector``: Cross-platform CPU core detection
- ``ProcessManager``: Forked process management with timeout
- ``ParallelSettings``: Configuration for worker counts

**Compiler** (``Compiler\Parallel``):

- ``ParallelCompiler``: Phase-based parallel compilation
- ``DocumentCompilationResult``: Serializable compilation results
- ``CompilationCacheInterface``: Cache state for parallel compilation

**Renderer** (``Renderer\Parallel``):

- ``ForkingRenderer``: Parallel Twig rendering with COW memory
- ``DocumentNavigationProvider``: Pre-computed prev/next navigation
- ``StaticDocumentIterator``: Thread-safe document iteration
- ``DirtyDocumentProvider``: Interface for incremental rendering

Core Utilities
==============

CPU Detection
-------------

The ``CpuDetector`` class provides cross-platform detection of available CPU
cores:

.. code-block:: php

    use phpDocumentor\Guides\Build\Parallel\CpuDetector;

    // Detect cores with default settings (max 8, default 4)
    $workerCount = CpuDetector::detectCores();

    // Customize limits
    $workerCount = CpuDetector::detectCores(
        maxWorkers: 16,      // Allow up to 16 workers
        defaultWorkers: 2,   // Use 2 if detection fails
    );

Detection methods (in order):

1. **Linux**: Reads ``/proc/cpuinfo``
2. **Linux/GNU**: Executes ``nproc``
3. **macOS/BSD**: Executes ``sysctl -n hw.ncpu``
4. **Fallback**: Returns the configured default

Process Management
------------------

The ``ProcessManager`` class provides utilities for managing forked processes:

.. code-block:: php

    use phpDocumentor\Guides\Build\Parallel\ProcessManager;

    // Fork workers
    $childPids = [];
    for ($i = 0; $i < $workerCount; $i++) {
        $pid = pcntl_fork();
        if ($pid === 0) {
            ProcessManager::clearTempFileTracking();
            processDocuments($i);
            exit(0);
        }
        $childPids[$i] = $pid;
    }

    // Wait with timeout
    $result = ProcessManager::waitForChildrenWithTimeout(
        $childPids,
        timeoutSeconds: 300,
    );

Key features:

- Non-blocking wait with configurable timeout
- Automatic SIGKILL for stuck processes
- Secure temp file creation (0600 permissions)
- Signal handlers for cleanup on SIGTERM/SIGINT

Parallel Settings
-----------------

Configure parallel processing behavior:

.. code-block:: php

    use phpDocumentor\Guides\Build\Parallel\ParallelSettings;

    $settings = new ParallelSettings();
    $settings->setWorkerCount(8);     // Explicit count
    $settings->setWorkerCount(0);     // Auto-detect
    $settings->setWorkerCount(-1);    // Disable (sequential)

    // Get effective count
    $workers = $settings->resolveWorkerCount(
        CpuDetector::detectCores()
    );

Parallel Compiler
=================

The ``ParallelCompiler`` splits compilation into phases based on shared state
dependencies:

**Phase 1 - Collection (parallel)**: priority ≥ 4900

  - DocumentEntryRegistrationTransformer, CollectLinkTargetsTransformer
  - Write to ProjectNode, don't read cross-document data
  - Results serialized via ``DocumentCompilationResult``

**Phase 2 - Merge (sequential)**: O(n)

  - Merge all DocumentCompilationResults into ProjectNode
  - Reconstruct toctree relationships from path-based data

**Phase 3 - Resolution (parallel)**: priority 1000-4500

  - Menu resolvers, citation resolvers
  - Read from complete ProjectNode, write to documents

**Phase 4 - Finalization (sequential)**: priority < 1000

  - AutomaticMenuPass, GlobalMenuPass, ToctreeValidationPass
  - Cross-document mutations

Usage:

.. code-block:: php

    use phpDocumentor\Guides\Compiler\Parallel\ParallelCompiler;

    $compiler = new ParallelCompiler(
        $sequentialCompiler,  // Fallback compiler
        $compilerPasses,
        $nodeTransformerFactory,
        $compilationCache,    // Optional, for incremental
        $logger,              // Optional
        $workerCount,         // Optional, null = auto-detect
    );

    $documents = $compiler->run($documents, $compilerContext);

Document Compilation Result
---------------------------

The ``DocumentCompilationResult`` class captures all data written to
ProjectNode during compilation, surviving serialization across process
boundaries:

.. code-block:: php

    // In child process:
    $result = DocumentCompilationResult::extractFromProjectNode($projectNode);

    // Contains:
    // - $documentEntries: All DocumentEntryNode objects
    // - $internalLinkTargets: Link target mappings
    // - $citationTargets: Citation references
    // - $toctreeRelationships: Path-based parent/child relations

    // In parent process:
    $result->mergeIntoProjectNode($parentProjectNode);

Parallel Renderer
=================

The ``ForkingRenderer`` implements ``TypeRenderer`` and parallelizes Twig
rendering:

.. code-block:: php

    use phpDocumentor\Guides\Renderer\Parallel\ForkingRenderer;

    $renderer = new ForkingRenderer(
        $commandBus,
        $navigationProvider,
        $dirtyDocumentProvider,  // Optional, for incremental
        $parallelSettings,       // Optional
        $logger,                 // Optional
    );

    $renderer->render($renderCommand);

Key design decisions:

1. **Fork after parsing**: AST is in memory, inherited via copy-on-write
2. **No write conflicts**: Each child renders to different output files
3. **Graceful fallback**: Sequential when pcntl unavailable or < 10 docs

Document Navigation Provider
----------------------------

When forking, each child renders a subset of documents, but prev/next
navigation needs knowledge of the full document order:

.. code-block:: php

    use phpDocumentor\Guides\Renderer\Parallel\DocumentNavigationProvider;

    $provider = new DocumentNavigationProvider();

    // Initialize BEFORE forking
    $provider->initializeFromArray($allDocuments);

    // After fork, in child process:
    $previous = $provider->getPreviousDocument($currentPath);
    $next = $provider->getNextDocument($currentPath);

The provider's state is inherited via copy-on-write and provides O(1) lookups
for any document in any child process.

Incremental Rendering Integration
---------------------------------

The ``DirtyDocumentProvider`` interface enables integration with incremental
build systems:

.. code-block:: php

    use phpDocumentor\Guides\Renderer\Parallel\DirtyDocumentProvider;

    class MyIncrementalProvider implements DirtyDocumentProvider
    {
        public function isIncrementalEnabled(): bool
        {
            return true;
        }

        public function computeDirtySet(): array
        {
            // Return paths of documents that need re-rendering
            return ['chapter1', 'chapter2'];
        }
    }

When provided to ``ForkingRenderer``, only dirty documents are rendered.

Best Practices
==============

1. **Limit worker count**: Use ``CpuDetector`` with appropriate ``maxWorkers``
   to avoid overloading the system

2. **Handle failures gracefully**: Always check ``$result['failures']`` and
   provide appropriate error handling

3. **Clear temp tracking in children**: Call ``clearTempFileTracking()`` in
   child processes to prevent cleanup conflicts

4. **Use appropriate timeouts**: Set timeouts based on expected workload to
   detect stuck processes

5. **Consider fallback**: Provide sequential fallback for systems without
   ``pcntl`` extension

6. **Initialize navigation before forking**: The ``DocumentNavigationProvider``
   must be initialized with the full document order before ``pcntl_fork()``

7. **Serialize data, not objects**: Use path-based relationships rather than
   object references across process boundaries

Memory Considerations
=====================

Parallel processing uses copy-on-write (COW) semantics:

- **Before fork**: Parent has parsed AST in memory
- **After fork**: Children share memory until they write
- **During rendering**: Each child's writes trigger COW copies

This means:

- Read-only access to shared data is efficient
- Write-heavy operations should be minimized pre-fork
- The ``DocumentNavigationProvider`` should be populated before forking
