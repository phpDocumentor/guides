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

final class IncrementalBuildStateTest extends TestCase
{
    public function testNewStateHasEmptyDependencyGraph(): void
    {
        $state = new IncrementalBuildState();

        self::assertInstanceOf(DependencyGraph::class, $state->getDependencyGraph());
        self::assertSame([], $state->getDependencyGraph()->getAllDocuments());
    }

    public function testSetAndGetExports(): void
    {
        $state = new IncrementalBuildState();
        $exports = new DocumentExports(
            documentPath: 'index',
            contentHash: 'abc123',
            exportsHash: 'def456',
            anchors: ['anchor1' => 'Title 1'],
            sectionTitles: ['section1' => 'Section 1'],
            citations: [],
            lastModified: 1_234_567_890,
            documentTitle: 'Index Page',
        );

        $state->setExports('index', $exports);

        self::assertSame($exports, $state->getExports('index'));
        self::assertNull($state->getExports('nonexistent'));
    }

    public function testGetAllExports(): void
    {
        $state = new IncrementalBuildState();
        $exports1 = new DocumentExports('doc1', 'hash1', 'exp1', [], [], [], 0, '');
        $exports2 = new DocumentExports('doc2', 'hash2', 'exp2', [], [], [], 0, '');

        $state->setExports('doc1', $exports1);
        $state->setExports('doc2', $exports2);

        $allExports = $state->getAllExports();

        self::assertCount(2, $allExports);
        self::assertSame($exports1, $allExports['doc1']);
        self::assertSame($exports2, $allExports['doc2']);
    }

    public function testPreviousExports(): void
    {
        $state = new IncrementalBuildState();
        $previousExports = [
            'doc1' => new DocumentExports('doc1', 'hash1', 'exp1', [], [], [], 0, ''),
            'doc2' => new DocumentExports('doc2', 'hash2', 'exp2', [], [], [], 0, ''),
        ];

        $state->setPreviousExports($previousExports);

        self::assertSame($previousExports, $state->getPreviousExports());
        self::assertSame($previousExports['doc1'], $state->getPreviousExportsFor('doc1'));
        self::assertNull($state->getPreviousExportsFor('nonexistent'));
    }

    public function testInputDir(): void
    {
        $state = new IncrementalBuildState();

        self::assertSame('', $state->getInputDir());

        $state->setInputDir('/path/to/docs');

        self::assertSame('/path/to/docs', $state->getInputDir());
    }

    public function testReset(): void
    {
        $state = new IncrementalBuildState();

        // Set up some state
        $state->setExports('doc1', new DocumentExports('doc1', 'hash1', 'exp1', [], [], [], 0, ''));
        $state->getDependencyGraph()->addImport('docA', 'docB');
        $previousExports = [
            'doc1' => new DocumentExports('doc1', 'old', 'old', [], [], [], 0, ''),
        ];
        $state->setPreviousExports($previousExports);

        $state->reset();

        // Exports and graph should be reset
        self::assertSame([], $state->getAllExports());
        self::assertSame([], $state->getDependencyGraph()->getAllDocuments());

        // But previous exports should be preserved (they're the reference for change detection)
        self::assertSame($previousExports, $state->getPreviousExports());
    }

    public function testSetDependencyGraph(): void
    {
        $state = new IncrementalBuildState();
        $newGraph = new DependencyGraph();
        $newGraph->addImport('docA', 'docB');

        $state->setDependencyGraph($newGraph);

        self::assertSame($newGraph, $state->getDependencyGraph());
        self::assertSame(['docB'], $state->getDependencyGraph()->getImports('docA'));
    }

    public function testSerializationRoundTrip(): void
    {
        $state = new IncrementalBuildState();

        // Use valid hash lengths: 32 chars (xxh128) and 64 chars (sha256)
        $contentHash = 'abc123abc123abc123abc123abc12345';
        $exportsHash = 'def456def456def456def456def456def456def456def456def456def456def4';

        // Add exports
        $exports = new DocumentExports(
            documentPath: 'index',
            contentHash: $contentHash,
            exportsHash: $exportsHash,
            anchors: ['anchor1' => 'Title 1'],
            sectionTitles: ['section1' => 'Section 1'],
            citations: ['citation1'],
            lastModified: 1_234_567_890,
            documentTitle: 'Index Page',
        );
        $state->setExports('index', $exports);

        // Add dependencies
        $state->getDependencyGraph()->addImport('docA', 'docB');
        $state->getDependencyGraph()->addImport('docA', 'index');

        // Serialize and restore
        $array = $state->toArray();
        $restored = IncrementalBuildState::fromArray($array);

        // Verify exports
        $restoredExports = $restored->getExports('index');
        self::assertNotNull($restoredExports);
        self::assertSame('index', $restoredExports->documentPath);
        self::assertSame($contentHash, $restoredExports->contentHash);
        self::assertSame($exportsHash, $restoredExports->exportsHash);
        self::assertSame(['anchor1' => 'Title 1'], $restoredExports->anchors);
        self::assertSame(['section1' => 'Section 1'], $restoredExports->sectionTitles);
        self::assertSame(['citation1'], $restoredExports->citations);
        self::assertSame(1_234_567_890, $restoredExports->lastModified);
        self::assertSame('Index Page', $restoredExports->documentTitle);

        // Verify dependency graph
        self::assertContains('docB', $restored->getDependencyGraph()->getImports('docA'));
        self::assertContains('index', $restored->getDependencyGraph()->getImports('docA'));
    }

    public function testFromArrayWithEmptyData(): void
    {
        $state = IncrementalBuildState::fromArray([]);

        self::assertSame([], $state->getAllExports());
        self::assertSame([], $state->getDependencyGraph()->getAllDocuments());
    }

    public function testSetExportsAllowsUpdatingExistingDocument(): void
    {
        $state = new IncrementalBuildState();
        $exports1 = new DocumentExports('doc1', 'hash1', 'exp1', [], [], [], 0, '');
        $exports2 = new DocumentExports('doc1', 'hash2', 'exp2', [], [], [], 0, '');

        $state->setExports('doc1', $exports1);
        $state->setExports('doc1', $exports2); // Update same document

        // Should use the latest exports
        self::assertSame($exports2, $state->getExports('doc1'));
        self::assertCount(1, $state->getAllExports());
    }

    public function testSetExportsThrowsWhenLimitExceeded(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed maximum');

        $state = new IncrementalBuildState();

        // Use reflection to directly set exports near the limit
        $reflection = new ReflectionClass($state);
        $property = $reflection->getProperty('exports');

        $exports = [];
        for ($i = 0; $i < 100_000; $i++) {
            $exports['doc' . $i] = new DocumentExports('doc' . $i, 'hash', 'exp', [], [], [], 0, '');
        }

        $property->setValue($state, $exports);

        // Now try to add one more - should throw
        $state->setExports('one-more-doc', new DocumentExports('one-more-doc', 'hash', 'exp', [], [], [], 0, ''));
    }

    public function testSetPreviousExportsThrowsWhenLimitExceeded(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed maximum');

        $state = new IncrementalBuildState();

        // Create array with more than MAX_EXPORTS (100000)
        $exports = [];
        for ($i = 0; $i < 100_001; $i++) {
            $exports['doc' . $i] = new DocumentExports('doc' . $i, 'hash', 'exp', [], [], [], 0, '');
        }

        $state->setPreviousExports($exports);
    }

    public function testSetHashAlgorithmAcceptsValidAlgorithms(): void
    {
        $state = new IncrementalBuildState();

        $state->setHashAlgorithm('sha256');
        self::assertSame('sha256', $state->getHashAlgorithm());

        $state->setHashAlgorithm('xxh128');
        self::assertSame('xxh128', $state->getHashAlgorithm());
    }

    public function testSetHashAlgorithmThrowsOnInvalidAlgorithm(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid hash algorithm "md5"');

        $state = new IncrementalBuildState();
        $state->setHashAlgorithm('md5');
    }

    public function testFromArrayThrowsOnInvalidHashAlgorithm(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid hash algorithm "crc32"');

        IncrementalBuildState::fromArray(['hashAlgorithm' => 'crc32']);
    }

    public function testFromArrayAcceptsEmptyHashAlgorithmForLegacyState(): void
    {
        $state = IncrementalBuildState::fromArray(['hashAlgorithm' => '']);

        self::assertSame('', $state->getHashAlgorithm());
    }

    public function testIsHashAlgorithmCompatibleWithMatchingAlgorithm(): void
    {
        $state = new IncrementalBuildState();
        $state->setHashAlgorithm('sha256');

        self::assertTrue($state->isHashAlgorithmCompatible('sha256'));
        self::assertFalse($state->isHashAlgorithmCompatible('xxh128'));
    }

    public function testIsHashAlgorithmCompatibleLegacyStateAssumeSha256(): void
    {
        // Legacy state has empty hashAlgorithm
        $state = new IncrementalBuildState();
        // Don't call setHashAlgorithm - this simulates legacy state

        // Legacy state should be compatible with sha256 (the old fallback)
        self::assertTrue($state->isHashAlgorithmCompatible('sha256'));
        // But NOT compatible with xxh128 (to force rebuild when xxh128 becomes available)
        self::assertFalse($state->isHashAlgorithmCompatible('xxh128'));
    }

    public function testHashAlgorithmSerializationRoundTrip(): void
    {
        $state = new IncrementalBuildState();
        $state->setHashAlgorithm('xxh128');

        $array = $state->toArray();
        $restored = IncrementalBuildState::fromArray($array);

        self::assertSame('xxh128', $restored->getHashAlgorithm());
        self::assertTrue($restored->isHashAlgorithmCompatible('xxh128'));
    }
}
