=================
Incremental Builds
=================

The guides library provides infrastructure for incremental builds, allowing
applications to skip re-rendering unchanged documents. This can dramatically
improve build times for large documentation sets.

This document covers usage and integration. For architecture details, security
considerations, and maintainer information, see :doc:`incremental-builds-architecture`.

Overview
========

Incremental build support consists of several components:

**Infrastructure Classes:**

- **DependencyGraph**: Tracks inter-document dependencies (includes, references)
- **ContentHasher**: Computes fast content hashes for change detection
- **ChangeDetector**: Determines which documents need re-rendering
- **DocumentExports**: Tracks exported symbols (anchors, titles, citations)
- **IncrementalBuildState**: Holds state during compilation

**Compiler Passes:**

- **ExportsCollectorPass**: Collects exports from each document during compilation
- **DependencyGraphPass**: Builds the dependency graph from cross-references

Dependency Graph
================

The ``DependencyGraph`` class tracks relationships between documents to enable
dirty propagation. When a document changes, all documents that depend on it
must also be re-rendered.

.. code-block:: php

    use phpDocumentor\Guides\Build\IncrementalBuild\DependencyGraph;

    $graph = new DependencyGraph();

    // Record that index.rst includes getting-started.rst
    $graph->addImport('index', 'getting-started');

    // Record that tutorials/basics.rst references index.rst
    $graph->addImport('tutorials/basics', 'index');

    // When index.rst changes, find all affected documents
    $dirtyDocs = ['index'];
    $allAffected = $graph->propagateDirty($dirtyDocs);
    // Returns: ['index', 'tutorials/basics']

Merging Graphs
--------------

When parsing documents in parallel, each worker builds a partial dependency graph.
These can be merged after parsing completes:

.. code-block:: php

    $mainGraph = new DependencyGraph();
    $workerGraph = new DependencyGraph();

    // ... workers add imports to their graphs ...

    $mainGraph->merge($workerGraph);

Persistence
-----------

The dependency graph can be serialized for caching between builds:

.. code-block:: php

    // Save to cache
    $data = $graph->toArray();
    file_put_contents('cache/deps.json', json_encode($data));

    // Load from cache
    $data = json_decode(file_get_contents('cache/deps.json'), true);
    $graph = DependencyGraph::fromArray($data);

Content Hasher
==============

The ``ContentHasher`` class provides fast content hashing using xxh128 (if available)
or SHA-256 as a fallback.

.. code-block:: php

    use phpDocumentor\Guides\Build\IncrementalBuild\ContentHasher;

    $hasher = new ContentHasher();

    // Hash a file
    $fileHash = $hasher->hashFile('/path/to/document.rst');

    // Hash string content
    $contentHash = $hasher->hashContent($documentContent);

    // Hash document exports (for dependency invalidation)
    $exportsHash = $hasher->hashExports(
        anchors: ['section-1' => 'Section One', 'section-2' => 'Section Two'],
        sectionTitles: ['section-1' => 'Section One'],
        citations: ['ref1', 'ref2'],
        documentTitle: 'My Document',
    );

Change Detection
================

The ``ChangeDetector`` class determines which documents need re-rendering by
comparing current file state against cached exports.

.. code-block:: php

    use phpDocumentor\Guides\Build\IncrementalBuild\ChangeDetector;
    use phpDocumentor\Guides\Build\IncrementalBuild\ContentHasher;

    $hasher = new ContentHasher();
    $detector = new ChangeDetector($hasher);

    // Get list of document paths
    $documentPaths = ['index', 'getting-started', 'tutorials/basics'];

    // Load cached exports from previous build
    $cachedExports = loadCachedExports(); // array<string, DocumentExports>

    // Resolver function to get file path from document path
    $fileResolver = fn(string $docPath) => "/docs/{$docPath}.rst";

    // Detect changes
    $result = $detector->detectChangesWithResolver(
        $documentPaths,
        $cachedExports,
        $fileResolver,
    );

    // Get documents that need re-rendering
    $changedDocs = $result->getChangedDocuments();
    $unchangedDocs = $result->getUnchangedDocuments();

Document Exports
================

The ``DocumentExports`` class tracks the "public interface" of a document -
the anchors, section titles, and citations it exports. When exports change,
dependent documents must be re-rendered even if their content hasn't changed.

.. code-block:: php

    use phpDocumentor\Guides\Build\IncrementalBuild\DocumentExports;

    $exports = new DocumentExports(
        documentPath: 'getting-started',
        contentHash: $hasher->hashFile($filePath),
        exportsHash: $hasher->hashExports($anchors, $titles, $citations),
        anchors: ['installation' => 'Installation', 'first-steps' => 'First Steps'],
        sectionTitles: ['installation' => 'Installation'],
        citations: [],
        lastModified: filemtime($filePath),
        documentTitle: 'Getting Started',
    );

    // Check if exports changed (triggers dependency re-render)
    if ($exports->hasExportsChanged($previousExports)) {
        // Dependent documents need re-rendering
    }

    // Check if content changed (document itself needs re-rendering)
    if ($exports->hasContentChanged($previousExports)) {
        // This document needs re-rendering
    }

Incremental Build State
=======================

The ``IncrementalBuildState`` class holds all incremental build state during a
single compilation run. It stores the dependency graph and document exports,
and can be serialized for persistence between builds.

.. code-block:: php

    use phpDocumentor\Guides\Build\IncrementalBuild\IncrementalBuildState;

    $state = new IncrementalBuildState();

    // Set the input directory for source file hashing
    $state->setInputDir('/path/to/docs');

    // Load previous exports for change detection
    $state->setPreviousExports($cachedExports);

    // After compilation, get the current state
    $graph = $state->getDependencyGraph();
    $exports = $state->getAllExports();

    // Serialize for caching
    $data = $state->toArray();
    file_put_contents('cache/build_state.json', json_encode($data));

    // Restore from cache
    $data = json_decode(file_get_contents('cache/build_state.json'), true);
    $state = IncrementalBuildState::fromArray($data);

Compiler Passes
===============

The library includes two compiler passes that automatically build the dependency
graph and collect exports during the compilation phase.

ExportsCollectorPass
--------------------

Priority: 10 (runs late, after all document and menu processing)

Collects anchors, section titles, citations, and document titles from each
compiled document. Computes content and exports hashes for change detection.

DependencyGraphPass
-------------------

Priority: 9 (runs after ``ExportsCollectorPass``)

Analyzes all documents for cross-references (``:doc:``, ``:ref:``, etc.) and
records dependencies in the graph. This enables dirty propagation when a
document's exports change.

Integration Example
===================

Here's a complete example of implementing incremental builds:

.. code-block:: php

    use phpDocumentor\Guides\Build\IncrementalBuild\ChangeDetector;
    use phpDocumentor\Guides\Build\IncrementalBuild\ContentHasher;
    use phpDocumentor\Guides\Build\IncrementalBuild\DependencyGraph;

    class IncrementalBuilder
    {
        private ContentHasher $hasher;
        private ChangeDetector $detector;
        private DependencyGraph $graph;

        public function __construct()
        {
            $this->hasher = new ContentHasher();
            $this->detector = new ChangeDetector($this->hasher);
            $this->graph = new DependencyGraph();
        }

        public function build(array $documentPaths, array $cachedExports): array
        {
            // 1. Detect changed documents
            $result = $this->detector->detectChangesWithResolver(
                $documentPaths,
                $cachedExports,
                fn($doc) => "docs/{$doc}.rst"
            );

            // 2. Propagate changes through dependency graph
            $dirtyDocs = $this->graph->propagateDirty(
                $result->getChangedDocuments()
            );

            // 3. Render only dirty documents
            foreach ($dirtyDocs as $docPath) {
                $this->renderDocument($docPath);
            }

            return $dirtyDocs;
        }
    }
