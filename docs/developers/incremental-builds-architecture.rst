====================================
Incremental Builds: Architecture
====================================

This document describes the internal architecture, design decisions, and
security considerations of the incremental build system. For usage documentation,
see :doc:`incremental-builds`.

Design Goals
============

The incremental build system was designed with these priorities:

1. **Correctness** - Never skip a document that needs re-rendering
2. **Performance** - O(1) operations where possible, efficient memory usage
3. **Security** - Prevent resource exhaustion and path traversal attacks
4. **Parallelization** - Support parallel compilation workflows

Architecture Overview
=====================

.. code-block:: text

    ┌─────────────────────────────────────────────────────────────┐
    │                    IncrementalBuildCache                     │
    │  (Orchestrates caching, persistence, and state management)   │
    └─────────────────────────────────────────────────────────────┘
                │                    │                    │
                ▼                    ▼                    ▼
    ┌───────────────────┐ ┌──────────────────┐ ┌─────────────────┐
    │  DependencyGraph  │ │  DocumentExports │ │  CacheVersioning│
    │  (Import/export   │ │  (Per-document   │ │  (Version       │
    │   relationships)  │ │   public API)    │ │   validation)   │
    └───────────────────┘ └──────────────────┘ └─────────────────┘
                │                    │
                ▼                    ▼
    ┌───────────────────┐ ┌──────────────────┐
    │  DirtyPropagator  │ │  ChangeDetector  │
    │  (Cascade dirty   │ │  (File-based     │
    │   state)          │ │   detection)     │
    └───────────────────┘ └──────────────────┘

Component Responsibilities
==========================

IncrementalBuildCache
---------------------

The central orchestrator for cache persistence. Uses **sharded storage** with
256 buckets (2-character hex prefix from MD5 hash) for efficient incremental saves.

**Design decisions:**

- Sharded storage: Only modified documents are rewritten, not the entire cache
- Hash-based filenames: Prevents path traversal and handles special characters
- Separate metadata file: ``_build_meta.json`` is always loaded; exports are lazy-loaded

DependencyGraph
---------------

Bidirectional graph tracking import/dependent relationships. Uses keyed arrays
for O(1) lookup performance.

**Design decisions:**

- Bidirectional: Stores both ``imports[A] = [B, C]`` and ``dependents[B] = [A]``
- Keyed arrays: ``$imports[$doc][$target] = true`` for O(1) add/remove/lookup
- Depth-limited traversal: Maximum 100 levels to prevent stack overflow on cycles

DirtyPropagator
---------------

Propagates dirty state through the dependency graph when exports change.

**Design decisions:**

- Uses ``SplQueue`` for O(1) enqueue/dequeue (vs ``array_shift`` which is O(n))
- Export comparison: Only propagates when *exports* change, not just content
- Visited tracking: Prevents infinite loops in cyclic dependencies

GlobalInvalidationDetector
--------------------------

Detects changes that require a full rebuild (config, theme, toctree structure).

**Design decisions:**

- Configurable patterns: Default patterns can be overridden per-project
- Directory patterns: Must match complete path segments (``foo/`` matches
  ``path/foo/bar`` but not ``prefix_foo/bar``)

Security Model
==============

The incremental build system processes untrusted cache files and must defend
against malicious input.

Resource Limits
---------------

All components enforce consistent limits to prevent memory exhaustion:

.. code-block:: php

    // Consistent across all classes
    MAX_DOCUMENTS = 100_000
    MAX_EXPORTS = 100_000
    MAX_OUTPUT_PATHS = 100_000
    MAX_PROPAGATION_VISITS = 100_000

    // DependencyGraph-specific
    MAX_TOTAL_EDGES = 2_000_000
    MAX_IMPORTS_PER_DOCUMENT = 1_000

    // GlobalInvalidationDetector
    MAX_PATTERN_LENGTH = 256

**Important:** These limits are intentionally kept in sync. If you change one,
consider whether related limits should also change.

Input Validation
----------------

All ``fromArray()`` deserialization methods validate:

1. **Type checking**: All values must match expected types
2. **Size limits**: Arrays must not exceed maximum sizes
3. **Format validation**: Hashes must be valid hex strings
4. **Character validation**: Document paths reject control characters

Path Traversal Prevention
-------------------------

The sharded cache system prevents path traversal attacks:

.. code-block:: php

    // Shard directory names validated with regex
    private function isValidShardName(string $name): bool
    {
        return preg_match('/^[0-9a-f]{2}$/', $name) === 1;
    }

    // Document paths become hash-based filenames
    $hash = md5($docPath);  // e.g., "d41d8cd98f00b204"
    $prefix = substr($hash, 0, 2);  // e.g., "d4"
    $filename = $hash . '.json';  // Full hash as filename

Thread Safety
=============

The incremental build classes are **NOT thread-safe**. They are designed for
single-threaded build processes.

For parallel builds, use the extract/merge pattern:

.. code-block:: php

    // Parent process
    $cache = new IncrementalBuildCache($versioning);
    $cache->load($outputDir);

    // Fork child processes, each with their own state
    foreach ($chunks as $chunk) {
        $childState = $state->extractState();
        // Pass $childState to child process
    }

    // After children complete, merge results sequentially
    foreach ($childResults as $result) {
        $cache->mergeState($result);
    }

    $cache->save($outputDir);

Algorithm Complexity
====================

.. list-table::
   :header-rows: 1

   * - Operation
     - Complexity
     - Notes
   * - ``DependencyGraph::addImport()``
     - O(1)
     - Keyed array insertion
   * - ``DependencyGraph::getImports()``
     - O(1)
     - Direct array access
   * - ``DependencyGraph::propagateDirty()``
     - O(V + E)
     - BFS traversal
   * - ``DirtyPropagator::propagate()``
     - O(V + E)
     - Uses SplQueue
   * - ``IncrementalBuildCache::save()``
     - O(dirty)
     - Only writes changed exports
   * - ``ChangeDetector::detectChanges()``
     - O(n)
     - Checks each document

Cache Format
============

The cache uses two storage formats:

Metadata File (``_build_meta.json``)
------------------------------------

Always loaded. Contains version info, dependency graph, and output paths.

.. code-block:: json

    {
        "metadata": {
            "version": 1,
            "phpVersion": "8.1.0",
            "packageVersion": "1.0.0",
            "settingsHash": "abc123...",
            "createdAt": 1706140800
        },
        "dependencies": {
            "imports": {"doc1": {"doc2": true}},
            "dependents": {"doc2": {"doc1": true}}
        },
        "outputs": {
            "doc1": "/output/doc1.html"
        }
    }

Export Files (``_exports/<hash-prefix>/<hash>.json``)
-----------------------------------------------------

Loaded on demand. One file per document, sharded into 256 directories.

.. code-block:: json

    {
        "path": "getting-started",
        "documentPath": "getting-started",
        "contentHash": "a1b2c3...",
        "exportsHash": "d4e5f6...",
        "anchors": {"installation": "Installation"},
        "sectionTitles": {"installation": "Installation"},
        "citations": [],
        "lastModified": 1706140800,
        "documentTitle": "Getting Started"
    }

Testing Guidelines
==================

When modifying the incremental build system:

1. **Security tests**: Add tests for limit enforcement and validation
2. **Edge cases**: Test cycles, empty graphs, maximum sizes
3. **Serialization round-trips**: Test ``toArray()``/``fromArray()`` compatibility
4. **Algorithm correctness**: Verify dirty propagation finds all affected documents

Example test patterns:

.. code-block:: php

    // Test limit enforcement
    public function testRejectsExcessiveDocuments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        // ... create data exceeding MAX_DOCUMENTS
    }

    // Test cycle handling
    public function testHandlesCyclicDependencies(): void
    {
        $graph->addImport('a', 'b');
        $graph->addImport('b', 'c');
        $graph->addImport('c', 'a');  // Cycle!

        $result = $graph->propagateDirty(['a']);
        // Should not infinite loop, should find all three
    }

Extending the System
====================

Adding New Dependency Types
---------------------------

To track a new type of cross-reference:

1. Update ``DependencyGraphPass`` to detect the new reference type
2. Call ``$graph->addImport($source, $target)`` for each reference
3. Add tests for the new reference detection

Adding New Export Types
-----------------------

To track additional exported symbols:

1. Update ``DocumentExports`` to include the new field
2. Update ``ExportsCollectorPass`` to collect the new data
3. Update ``ContentHasher::hashExports()`` to include in the hash
4. Add tests for export change detection
