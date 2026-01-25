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
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function iterator_to_array;
use function sort;

final class DependencyGraphTest extends TestCase
{
    public function testAddImportCreatesEdges(): void
    {
        $graph = new DependencyGraph();
        $graph->addImport('docA', 'docB');

        self::assertSame(['docB'], $graph->getImports('docA'));
        self::assertSame(['docA'], $graph->getDependents('docB'));
    }

    public function testSelfReferencesAreIgnored(): void
    {
        $graph = new DependencyGraph();
        $graph->addImport('docA', 'docA');

        self::assertSame([], $graph->getImports('docA'));
        self::assertSame([], $graph->getDependents('docA'));
    }

    public function testPropagateDirtyFindsDependents(): void
    {
        $graph = new DependencyGraph();
        // A imports B, C imports A
        $graph->addImport('docA', 'docB');
        $graph->addImport('docC', 'docA');

        // If B is dirty, both A and C should be dirty (transitive)
        $dirty = $graph->propagateDirty(['docB']);

        self::assertContains('docB', $dirty);
        self::assertContains('docA', $dirty);
        self::assertContains('docC', $dirty);
        self::assertCount(3, $dirty);
    }

    public function testPropagateDirtyHandlesCycles(): void
    {
        $graph = new DependencyGraph();
        // Circular: A -> B -> C -> A
        $graph->addImport('docA', 'docB');
        $graph->addImport('docB', 'docC');
        $graph->addImport('docC', 'docA');

        // Should not infinite loop
        $dirty = $graph->propagateDirty(['docA']);

        self::assertContains('docA', $dirty);
        self::assertContains('docB', $dirty);
        self::assertContains('docC', $dirty);
        self::assertCount(3, $dirty);
    }

    public function testRemoveDocumentClearsAllEdges(): void
    {
        $graph = new DependencyGraph();
        $graph->addImport('docA', 'docB');
        $graph->addImport('docC', 'docA');

        $graph->removeDocument('docA');

        self::assertSame([], $graph->getImports('docA'));
        self::assertSame([], $graph->getDependents('docA'));
        self::assertSame([], $graph->getDependents('docB')); // A was removed
        self::assertSame([], $graph->getImports('docC')); // A was removed
    }

    public function testClearImportsForRemovesOnlyImports(): void
    {
        $graph = new DependencyGraph();
        $graph->addImport('docA', 'docB');
        $graph->addImport('docA', 'docC');
        $graph->addImport('docD', 'docA'); // A is also a dependency

        $graph->clearImportsFor('docA');

        self::assertSame([], $graph->getImports('docA'));
        self::assertSame([], $graph->getDependents('docB'));
        self::assertSame([], $graph->getDependents('docC'));
        // But A should still be a dependency for D
        self::assertSame(['docA'], $graph->getImports('docD'));
    }

    public function testSerializationRoundTrip(): void
    {
        $graph = new DependencyGraph();
        $graph->addImport('docA', 'docB');
        $graph->addImport('docA', 'docC');
        $graph->addImport('docD', 'docA');

        $array = $graph->toArray();
        $restored = DependencyGraph::fromArray($array);

        self::assertSame($graph->getImports('docA'), $restored->getImports('docA'));
        self::assertSame($graph->getDependents('docA'), $restored->getDependents('docA'));
        self::assertSame($graph->getDependents('docB'), $restored->getDependents('docB'));
    }

    public function testMerge(): void
    {
        $graph1 = new DependencyGraph();
        $graph1->addImport('docA', 'docB');

        $graph2 = new DependencyGraph();
        $graph2->addImport('docC', 'docD');
        $graph2->addImport('docA', 'docE'); // Additional import for docA

        $graph1->merge($graph2);

        self::assertContains('docB', $graph1->getImports('docA'));
        self::assertContains('docE', $graph1->getImports('docA'));
        self::assertSame(['docD'], $graph1->getImports('docC'));
    }

    public function testGetAllDocuments(): void
    {
        $graph = new DependencyGraph();
        $graph->addImport('docA', 'docB');
        $graph->addImport('docC', 'docD');

        $allDocs = $graph->getAllDocuments();

        self::assertContains('docA', $allDocs);
        self::assertContains('docB', $allDocs);
        self::assertContains('docC', $allDocs);
        self::assertContains('docD', $allDocs);
    }

    public function testGetStats(): void
    {
        $graph = new DependencyGraph();
        $graph->addImport('docA', 'docB');
        $graph->addImport('docA', 'docC');
        $graph->addImport('docD', 'docA');

        $stats = $graph->getStats();

        self::assertSame(4, $stats['documents']);
        self::assertSame(3, $stats['edges']);
    }

    public function testFromArrayThrowsOnInvalidImports(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected imports to be array');

        DependencyGraph::fromArray(['imports' => 'not-an-array']);
    }

    public function testFromArrayThrowsOnInvalidDependents(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected dependents to be array');

        DependencyGraph::fromArray(['dependents' => 123]);
    }

    public function testFromArrayThrowsOnInvalidImportValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected import value for "docA" to be array');

        DependencyGraph::fromArray(['imports' => ['docA' => 'not-an-array']]);
    }

    public function testFromArrayThrowsOnInvalidImportTarget(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected import target for "docA" to be string');

        DependencyGraph::fromArray(['imports' => ['docA' => [123]]]);
    }

    public function testFromArrayThrowsOnInvalidDependentValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected dependent value for "docB" to be array');

        DependencyGraph::fromArray(['dependents' => ['docB' => 'not-an-array']]);
    }

    public function testFromArrayThrowsOnInvalidDependentSource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected dependent source for "docB" to be string');

        DependencyGraph::fromArray(['dependents' => ['docB' => [456]]]);
    }

    public function testPropagateDirtyIteratorYieldsSameResultsAsPropageDirty(): void
    {
        $graph = new DependencyGraph();
        $graph->addImport('docA', 'docB');
        $graph->addImport('docC', 'docA');
        $graph->addImport('docD', 'docC');

        $arrayResult = $graph->propagateDirty(['docB']);
        $iteratorResult = iterator_to_array($graph->propagateDirtyIterator(['docB']));

        // Both should return the same documents (order may differ)
        sort($arrayResult);
        sort($iteratorResult);
        self::assertSame($arrayResult, $iteratorResult);
    }

    public function testGetStatsOnEmptyGraph(): void
    {
        $graph = new DependencyGraph();
        $stats = $graph->getStats();

        self::assertSame(0, $stats['documents']);
        self::assertSame(0, $stats['edges']);
        self::assertSame(0.0, $stats['avgImportsPerDoc']);
    }

    public function testFromArrayHandlesIntegerKeys(): void
    {
        // JSON decode turns numeric string keys like "123" into integer keys
        // This simulates what happens when you json_decode a graph with numeric document paths
        $data = [
            'imports' => [123 => ['doc456', 'doc789']],
            'dependents' => ['doc456' => ['123'], 'doc789' => ['123']],
        ];

        $graph = DependencyGraph::fromArray($data);

        // Should work correctly with integer-to-string key conversion
        self::assertSame(['doc456', 'doc789'], $graph->getImports('123'));
        self::assertSame(['123'], $graph->getDependents('doc456'));
    }

    public function testFromArrayThrowsOnExcessiveImportsPerDocument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed maximum');

        // Create array with more than MAX_IMPORTS_PER_DOCUMENT (1000) for one doc
        $targets = [];
        for ($i = 0; $i < 1001; $i++) {
            $targets[] = 'target' . $i;
        }

        DependencyGraph::fromArray(['imports' => ['docA' => $targets]]);
    }

    public function testFromArrayThrowsOnExcessiveDependentsPerDocument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed maximum');

        // Create array with more than MAX_IMPORTS_PER_DOCUMENT (1000) dependents
        $sources = [];
        for ($i = 0; $i < 1001; $i++) {
            $sources[] = 'source' . $i;
        }

        DependencyGraph::fromArray(['dependents' => ['docB' => $sources]]);
    }

    public function testToArraySerializesNumericKeysAsStrings(): void
    {
        $graph = new DependencyGraph();
        $graph->addImport('123', '456');

        $array = $graph->toArray();

        // Keys should be strings, not integers
        $imports = $array['imports'];
        $dependents = $array['dependents'];

        self::assertArrayHasKey('123', $imports);
        self::assertContains('456', $imports['123'] ?? []);
        self::assertArrayHasKey('456', $dependents);
        self::assertContains('123', $dependents['456'] ?? []);
    }

    public function testAddImportReturnsTrueOnSuccess(): void
    {
        $graph = new DependencyGraph();

        self::assertTrue($graph->addImport('docA', 'docB'));
        self::assertSame(['docB'], $graph->getImports('docA'));
    }

    public function testAddImportReturnsTrueForSelfReference(): void
    {
        $graph = new DependencyGraph();

        // Self-references are silently ignored, not an error
        self::assertTrue($graph->addImport('docA', 'docA'));
        self::assertSame([], $graph->getImports('docA'));
    }

    public function testAddImportReturnsTrueForDuplicateEdge(): void
    {
        $graph = new DependencyGraph();
        $graph->addImport('docA', 'docB');

        // Adding same edge again should succeed (idempotent)
        self::assertTrue($graph->addImport('docA', 'docB'));
    }

    public function testValidateLimitsPassesForValidGraph(): void
    {
        $graph = new DependencyGraph();
        $graph->addImport('docA', 'docB');
        $graph->addImport('docA', 'docC');

        // Should not throw
        $graph->validateLimits();
        self::assertTrue(true); // Reached here without exception
    }

    public function testValidateLimitsThrowsOnExcessiveDocuments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed maximum');

        // Create a graph that exceeds MAX_DOCUMENTS via fromArray (which allows direct setting)
        $imports = [];
        for ($i = 0; $i < 100_001; $i++) {
            $imports['doc' . $i] = [];
        }

        // Use reflection to directly set imports to bypass addImport limits
        $graph = new DependencyGraph();
        $reflection = new ReflectionClass($graph);
        $property = $reflection->getProperty('imports');
        $property->setValue($graph, $imports);

        $graph->validateLimits();
    }

    public function testValidateLimitsThrowsOnExcessiveImportsPerDocument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed maximum');

        // Create a graph with too many imports for one document
        $toMap = [];
        for ($i = 0; $i < 1001; $i++) {
            $toMap['target' . $i] = true;
        }

        $graph = new DependencyGraph();
        $reflection = new ReflectionClass($graph);
        $property = $reflection->getProperty('imports');
        $property->setValue($graph, ['docA' => $toMap]);

        $graph->validateLimits();
    }

    public function testMergeAndValidateLimits(): void
    {
        $graph1 = new DependencyGraph();
        $graph1->addImport('docA', 'docB');

        $graph2 = new DependencyGraph();
        $graph2->addImport('docC', 'docD');

        $graph1->merge($graph2);
        $graph1->validateLimits(); // Should not throw

        self::assertContains('docB', $graph1->getImports('docA'));
        self::assertContains('docD', $graph1->getImports('docC'));
    }

    public function testFromArrayThrowsOnExcessiveDocumentCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed maximum');

        // Create array with more than MAX_DOCUMENTS (100000) imports entries
        $imports = [];
        for ($i = 0; $i < 100_001; $i++) {
            $imports['doc' . $i] = [];
        }

        DependencyGraph::fromArray(['imports' => $imports]);
    }

    public function testValidateLimitsThrowsOnExcessiveDependentsDocumentCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('dependents exceed maximum');

        // Create a graph with excessive dependents via reflection
        $dependents = [];
        for ($i = 0; $i < 100_001; $i++) {
            $dependents['doc' . $i] = [];
        }

        $graph = new DependencyGraph();
        $reflection = new ReflectionClass($graph);
        $property = $reflection->getProperty('dependents');
        $property->setValue($graph, $dependents);

        $graph->validateLimits();
    }

    public function testValidateLimitsThrowsOnExcessiveDependentsPerDocument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('dependents for');

        // Create a graph with too many dependents for one document
        $fromMap = [];
        for ($i = 0; $i < 1001; $i++) {
            $fromMap['source' . $i] = true;
        }

        $graph = new DependencyGraph();
        $reflection = new ReflectionClass($graph);
        $property = $reflection->getProperty('dependents');
        $property->setValue($graph, ['docTarget' => $fromMap]);

        $graph->validateLimits();
    }

    public function testValidateLimitsThrowsOnExcessiveTotalEdges(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('total edges');

        // Create a graph with excessive total edges via reflection
        // We can't easily create 2M+ edges, so we'll set edgeCount directly
        $graph = new DependencyGraph();
        $reflection = new ReflectionClass($graph);
        $edgeProperty = $reflection->getProperty('edgeCount');
        $edgeProperty->setValue($graph, 2_000_001); // Just over MAX_TOTAL_EDGES

        $graph->validateLimits();
    }

    public function testFromArrayThrowsOnExcessiveTotalEdges(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('total edges');

        // Create a graph structure with many small edge lists that sum to >2M
        // This is more realistic than 2M+ edges in one document
        // We'll create 2001 documents with 1000 edges each = 2,001,000 edges
        $imports = [];
        for ($doc = 0; $doc < 2001; $doc++) {
            $targets = [];
            for ($target = 0; $target < 1000; $target++) {
                $targets[] = 'target_' . $doc . '_' . $target;
            }

            $imports['doc' . $doc] = $targets;
        }

        DependencyGraph::fromArray(['imports' => $imports]);
    }

    public function testAddImportReturnsFalseWhenTotalEdgeLimitReached(): void
    {
        $graph = new DependencyGraph();
        $reflection = new ReflectionClass($graph);
        $edgeProperty = $reflection->getProperty('edgeCount');
        $edgeProperty->setValue($graph, 2_000_000); // At MAX_TOTAL_EDGES

        // Should return false when limit is reached
        self::assertFalse($graph->addImport('docNew', 'docTarget'));
    }

    /**
     * Stress test: verify graph operations work correctly near MAX_DOCUMENTS limit.
     *
     * This test creates a graph with 1000 documents (scaled down from 100k for
     * test performance) and verifies that all operations work correctly at scale.
     *
     * @group stress
     */
    public function testStressTestNearMaxDocumentsLimit(): void
    {
        $graph = new DependencyGraph();
        $docCount = 1000; // Scaled down from MAX_DOCUMENTS (100k) for test speed

        // Create a chain of dependencies: doc0 <- doc1 <- doc2 <- ... <- doc999
        // This creates a worst-case propagation scenario (linear chain)
        for ($i = 1; $i < $docCount; $i++) {
            $result = $graph->addImport('doc' . $i, 'doc' . ($i - 1));
            self::assertTrue($result, 'Failed to add import at index ' . $i);
        }

        // Verify graph stats
        $stats = $graph->getStats();
        self::assertSame($docCount, $stats['documents']);
        self::assertSame($docCount - 1, $stats['edges']);

        // Verify propagation from root finds all dependents
        $dirty = $graph->propagateDirty(['doc0']);
        self::assertCount($docCount, $dirty, 'Propagation should find all documents in chain');

        // Verify propagation from middle finds downstream only
        $midpoint = (int) ($docCount / 2);
        $dirty = $graph->propagateDirty(['doc' . $midpoint]);
        self::assertCount($docCount - $midpoint, $dirty, 'Propagation should find downstream documents');

        // Verify serialization round-trip preserves graph
        $serialized = $graph->toArray();
        $restored = DependencyGraph::fromArray($serialized);
        self::assertSame($stats, $restored->getStats());

        // Verify removal works correctly
        $graph->removeDocument('doc' . $midpoint);
        $stats = $graph->getStats();
        self::assertSame($docCount - 1, $stats['documents']);
    }

    /**
     * Stress test: verify graph handles fan-out pattern (one doc referenced by many).
     *
     * @group stress
     */
    public function testStressTestFanOutPattern(): void
    {
        $graph = new DependencyGraph();
        $fanOutCount = 500; // Number of documents importing from a single source

        // Create fan-out: many documents import from 'shared'
        for ($i = 0; $i < $fanOutCount; $i++) {
            $result = $graph->addImport('consumer' . $i, 'shared');
            self::assertTrue($result);
        }

        // Verify all consumers are dependents of 'shared'
        $dependents = $graph->getDependents('shared');
        self::assertCount($fanOutCount, $dependents);

        // Verify propagation from 'shared' finds all consumers
        $dirty = $graph->propagateDirty(['shared']);
        self::assertCount($fanOutCount + 1, $dirty); // +1 for 'shared' itself

        // Verify iterator version produces same results
        $dirtyIterator = iterator_to_array($graph->propagateDirtyIterator(['shared']));
        sort($dirty);
        sort($dirtyIterator);
        self::assertSame($dirty, $dirtyIterator);
    }
}
