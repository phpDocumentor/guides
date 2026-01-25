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

use PHPUnit\Framework\TestCase;

use function md5;

final class DirtyPropagatorTest extends TestCase
{
    private DirtyPropagator $propagator;

    protected function setUp(): void
    {
        $this->propagator = new DirtyPropagator();
    }

    public function testPropagateWithNoChanges(): void
    {
        $changes = new ChangeDetectionResult(
            dirty: [],
            clean: ['doc1', 'doc2'],
            new: [],
            deleted: [],
        );

        $graph = new DependencyGraph();
        $result = $this->propagator->propagate($changes, $graph, [], []);

        self::assertSame([], $result->documentsToRender);
        self::assertSame(['doc1', 'doc2'], $result->documentsToSkip);
    }

    public function testPropagateWithDirtyDocument(): void
    {
        $changes = new ChangeDetectionResult(
            dirty: ['doc1'],
            clean: ['doc2'],
            new: [],
            deleted: [],
        );

        $graph = new DependencyGraph();
        $result = $this->propagator->propagate($changes, $graph, [], []);

        self::assertContains('doc1', $result->documentsToRender);
        self::assertSame(['doc2'], $result->documentsToSkip);
    }

    public function testPropagateWithNewDocument(): void
    {
        $changes = new ChangeDetectionResult(
            dirty: [],
            clean: ['doc1'],
            new: ['doc2'],
            deleted: [],
        );

        $graph = new DependencyGraph();
        $result = $this->propagator->propagate($changes, $graph, [], []);

        self::assertContains('doc2', $result->documentsToRender);
        self::assertSame(['doc1'], $result->documentsToSkip);
    }

    public function testPropagateWithDeletedDocument(): void
    {
        $changes = new ChangeDetectionResult(
            dirty: [],
            clean: ['doc2'],
            new: [],
            deleted: ['doc1'],
        );

        // doc2 imports from doc1 (deleted)
        $graph = new DependencyGraph();
        $graph->addImport('doc2', 'doc1');

        $result = $this->propagator->propagate($changes, $graph, [], []);

        // doc2 should be dirty because its dependency was deleted
        self::assertContains('doc2', $result->documentsToRender);
        self::assertContains('doc1', $result->propagatedFrom);
    }

    public function testPropagateWithExportChange(): void
    {
        $changes = new ChangeDetectionResult(
            dirty: ['doc1'],
            clean: ['doc2'],
            new: [],
            deleted: [],
        );

        // doc2 imports from doc1
        $graph = new DependencyGraph();
        $graph->addImport('doc2', 'doc1');

        // Exports changed for doc1
        $oldExports = [
            'doc1' => $this->createExports('anchor1'),
            'doc2' => $this->createExports('anchor2'),
        ];
        $newExports = [
            'doc1' => $this->createExports('anchor1-changed'), // Different anchors
        ];

        $result = $this->propagator->propagate($changes, $graph, $oldExports, $newExports);

        // Both docs should be rendered because doc1's exports changed
        self::assertContains('doc1', $result->documentsToRender);
        self::assertContains('doc2', $result->documentsToRender);
        self::assertContains('doc1', $result->propagatedFrom);
    }

    public function testPropagateWithUnchangedExports(): void
    {
        $changes = new ChangeDetectionResult(
            dirty: ['doc1'],
            clean: ['doc2'],
            new: [],
            deleted: [],
        );

        // doc2 imports from doc1
        $graph = new DependencyGraph();
        $graph->addImport('doc2', 'doc1');

        // Same exports for doc1
        $exports = $this->createExports('anchor1');
        $oldExports = ['doc1' => $exports, 'doc2' => $this->createExports('anchor2')];
        $newExports = ['doc1' => $exports]; // Same object = same exports hash

        $result = $this->propagator->propagate($changes, $graph, $oldExports, $newExports);

        // Only doc1 should render, doc2's dependency exports unchanged
        self::assertContains('doc1', $result->documentsToRender);
        self::assertSame(['doc2'], $result->documentsToSkip);
    }

    public function testPropagateHandlesCycles(): void
    {
        $changes = new ChangeDetectionResult(
            dirty: ['doc1'],
            clean: [],
            new: [],
            deleted: [],
        );

        // Circular dependency: doc1 -> doc2 -> doc3 -> doc1
        $graph = new DependencyGraph();
        $graph->addImport('doc1', 'doc2');
        $graph->addImport('doc2', 'doc3');
        $graph->addImport('doc3', 'doc1');

        // All have changed exports to trigger full propagation
        $result = $this->propagator->propagate($changes, $graph, [], []);

        // Should not infinite loop and all should be marked for rendering
        self::assertContains('doc1', $result->documentsToRender);
        // doc2 and doc3 will be rendered because they depend on doc1 transitively
    }

    public function testPropagateTransitive(): void
    {
        $changes = new ChangeDetectionResult(
            dirty: ['doc1'],
            clean: ['doc2', 'doc3'],
            new: [],
            deleted: [],
        );

        // Chain: doc3 imports doc2, doc2 imports doc1
        $graph = new DependencyGraph();
        $graph->addImport('doc2', 'doc1');
        $graph->addImport('doc3', 'doc2');

        // doc1 exports changed, should propagate to doc2, then to doc3
        $oldExports = [
            'doc1' => $this->createExports('a'),
            'doc2' => $this->createExports('b'),
            'doc3' => $this->createExports('c'),
        ];
        $newExports = [
            'doc1' => $this->createExports('a-changed'),
            'doc2' => $this->createExports('b-changed'),
        ];

        $result = $this->propagator->propagate($changes, $graph, $oldExports, $newExports);

        self::assertContains('doc1', $result->documentsToRender);
        self::assertContains('doc2', $result->documentsToRender);
        self::assertContains('doc3', $result->documentsToRender);
    }

    public function testPropagateSimple(): void
    {
        $graph = new DependencyGraph();
        $graph->addImport('docA', 'docB');
        $graph->addImport('docC', 'docA');

        $result = $this->propagator->propagateSimple(['docB'], $graph);

        self::assertContains('docB', $result);
        self::assertContains('docA', $result); // Depends on docB
        self::assertContains('docC', $result); // Depends on docA
    }

    public function testPropagateSimpleWithNoDependents(): void
    {
        $graph = new DependencyGraph();
        $graph->addImport('docA', 'docB');

        // docB has no dependents except docA
        $result = $this->propagator->propagateSimple(['docC'], $graph);

        // Only docC is dirty, nothing depends on it
        self::assertSame(['docC'], $result);
    }

    public function testSavingsRatioCalculation(): void
    {
        $changes = new ChangeDetectionResult(
            dirty: ['doc1'],
            clean: ['doc2', 'doc3', 'doc4'],
            new: [],
            deleted: [],
        );

        $graph = new DependencyGraph();
        $result = $this->propagator->propagate($changes, $graph, [], []);

        // 1 to render, 3 to skip = 75% savings
        self::assertEqualsWithDelta(0.75, $result->getSavingsRatio(), 0.001);
    }

    private function createExports(string $anchor): DocumentExports
    {
        return new DocumentExports(
            documentPath: 'test',
            contentHash: md5($anchor),
            exportsHash: md5($anchor . '-exports'),
            anchors: [$anchor => 'Title'],
            sectionTitles: [],
            citations: [],
            lastModified: 0,
        );
    }
}
