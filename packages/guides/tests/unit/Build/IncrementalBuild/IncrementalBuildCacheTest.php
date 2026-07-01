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

use function file_put_contents;
use function is_dir;
use function json_encode;
use function mkdir;
use function rmdir;
use function scandir;
use function str_repeat;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

use const JSON_THROW_ON_ERROR;

final class IncrementalBuildCacheTest extends TestCase
{
    private string $tempDir;
    private CacheVersioning $versioning;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/incremental-cache-test-' . uniqid();
        mkdir($this->tempDir, 0o755, true);
        $this->versioning = new CacheVersioning();
    }

    protected function tearDown(): void
    {
        $this->recursiveDelete($this->tempDir);
    }

    private function recursiveDelete(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->recursiveDelete($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    public function testLoadReturnsFalseWithNoCache(): void
    {
        $cache = new IncrementalBuildCache($this->versioning);

        self::assertFalse($cache->load($this->tempDir));
        self::assertFalse($cache->isLoaded());
    }

    public function testSaveAndLoadRoundTrip(): void
    {
        $cache = new IncrementalBuildCache($this->versioning);

        // Add some data
        $exports = new DocumentExports(
            documentPath: 'index',
            contentHash: 'abc123abc123abc123abc123abc12345',
            exportsHash: 'def456def456def456def456def456def456def456def456def456def456def4',
            anchors: ['anchor1' => 'Title'],
            sectionTitles: ['section1' => 'Section'],
            citations: ['cite1'],
            lastModified: 1_234_567_890,
            documentTitle: 'Index',
        );
        $cache->setExports('index', $exports);
        $cache->setOutputPath('index', '/output/index.html');
        $cache->getDependencyGraph()->addImport('index', 'chapter1');

        // Save
        $cache->save($this->tempDir, 'settings-hash');

        // Load into new cache instance
        $cache2 = new IncrementalBuildCache($this->versioning);
        self::assertTrue($cache2->load($this->tempDir));
        self::assertTrue($cache2->isLoaded());

        // Verify data
        $loadedExports = $cache2->getExports('index');
        self::assertNotNull($loadedExports);
        self::assertSame('index', $loadedExports->documentPath);
        self::assertSame(['anchor1' => 'Title'], $loadedExports->anchors);

        self::assertSame('/output/index.html', $cache2->getOutputPath('index'));
        self::assertSame(['chapter1'], $cache2->getDependencyGraph()->getImports('index'));
    }

    public function testShardedExportStorage(): void
    {
        $cache = new IncrementalBuildCache($this->versioning);

        // Add multiple exports to test sharding
        for ($i = 0; $i < 10; $i++) {
            $cache->setExports('doc' . $i, new DocumentExports(
                documentPath: 'doc' . $i,
                contentHash: str_repeat('a', 32),
                exportsHash: str_repeat('b', 64),
                anchors: [],
                sectionTitles: [],
                citations: [],
                lastModified: 0,
            ));
        }

        $cache->save($this->tempDir);

        // Verify _exports directory exists
        self::assertDirectoryExists($this->tempDir . '/_exports');

        // Load and verify
        $cache2 = new IncrementalBuildCache($this->versioning);
        self::assertTrue($cache2->load($this->tempDir));
        self::assertCount(10, $cache2->getAllExports());
    }

    public function testIncrementalSaveOnlyWritesDirtyExports(): void
    {
        $cache = new IncrementalBuildCache($this->versioning);
        $cache->setExports('doc1', $this->createExports('doc1'));
        $cache->setExports('doc2', $this->createExports('doc2'));
        $cache->save($this->tempDir);

        // Modify only doc1
        $cache->setExports('doc1', $this->createExports('doc1-modified'));
        $cache->save($this->tempDir);

        // Both should still be loadable
        $cache2 = new IncrementalBuildCache($this->versioning);
        self::assertTrue($cache2->load($this->tempDir));
        self::assertNotNull($cache2->getExports('doc1'));
        self::assertNotNull($cache2->getExports('doc2'));
    }

    public function testRemoveDocumentDeletesShardFile(): void
    {
        $cache = new IncrementalBuildCache($this->versioning);
        $cache->setExports('doc1', $this->createExports('doc1'));
        $cache->setOutputPath('doc1', '/out/doc1.html');
        $cache->save($this->tempDir);

        // Verify file exists
        self::assertNotNull($cache->getExports('doc1'));

        // Remove document
        $cache->removeDocument('doc1');

        self::assertNull($cache->getExports('doc1'));
        self::assertNull($cache->getOutputPath('doc1'));
    }

    public function testClear(): void
    {
        $cache = new IncrementalBuildCache($this->versioning);
        $cache->setExports('doc1', $this->createExports('doc1'));
        $cache->setOutputPath('doc1', '/out/doc1.html');
        $cache->getDependencyGraph()->addImport('doc1', 'doc2');
        $cache->save($this->tempDir);

        $cache->clear();

        self::assertSame([], $cache->getAllExports());
        self::assertNull($cache->getOutputPath('doc1'));
        self::assertSame([], $cache->getDependencyGraph()->getAllDocuments());
        self::assertFalse($cache->isLoaded());
    }

    public function testGetStats(): void
    {
        $cache = new IncrementalBuildCache($this->versioning);
        $cache->setExports('doc1', $this->createExports('doc1'));
        $cache->setExports('doc2', $this->createExports('doc2'));
        $cache->setOutputPath('doc1', '/out/doc1.html');
        $cache->getDependencyGraph()->addImport('doc1', 'doc2');

        $stats = $cache->getStats();

        self::assertSame(2, $stats['documents']);
        self::assertSame(1, $stats['outputs']);
        self::assertFalse($stats['loaded']);
        self::assertIsArray($stats['graph']);
    }

    public function testExtractAndMergeState(): void
    {
        $cache1 = new IncrementalBuildCache($this->versioning);
        $cache1->setExports('doc1', $this->createExports('doc1'));
        $cache1->setOutputPath('doc1', '/out/doc1.html');
        $cache1->getDependencyGraph()->addImport('doc1', 'doc2');

        $state = $cache1->extractState();

        // Merge into new cache
        $cache2 = new IncrementalBuildCache($this->versioning);
        $cache2->mergeState($state);

        self::assertNotNull($cache2->getExports('doc1'));
        self::assertSame('/out/doc1.html', $cache2->getOutputPath('doc1'));
        self::assertSame(['doc2'], $cache2->getDependencyGraph()->getImports('doc1'));
    }

    public function testMergeStateDoesNotOverwriteExisting(): void
    {
        $cache = new IncrementalBuildCache($this->versioning);
        $cache->setExports('doc1', $this->createExports('original'));
        $cache->setOutputPath('doc1', '/original/path.html');

        $state = [
            'exports' => ['doc1' => $this->createExports('new')->toArray()],
            'outputPaths' => ['doc1' => '/new/path.html'],
        ];

        $cache->mergeState($state);

        // Original values should be preserved
        self::assertSame('/original/path.html', $cache->getOutputPath('doc1'));
    }

    public function testGetSettingsHash(): void
    {
        $cache = new IncrementalBuildCache($this->versioning);
        $cache->save($this->tempDir, 'my-settings-hash');

        $cache2 = new IncrementalBuildCache($this->versioning);
        $cache2->load($this->tempDir);

        self::assertSame('my-settings-hash', $cache2->getSettingsHash());
    }

    public function testInputDir(): void
    {
        $cache = new IncrementalBuildCache($this->versioning);

        self::assertSame('', $cache->getInputDir());

        $cache->setInputDir('/path/to/docs');

        self::assertSame('/path/to/docs', $cache->getInputDir());
    }

    public function testGetAllDocPaths(): void
    {
        $cache = new IncrementalBuildCache($this->versioning);
        $cache->setExports('doc1', $this->createExports('doc1'));
        $cache->setExports('doc2', $this->createExports('doc2'));
        $cache->setExports('doc3', $this->createExports('doc3'));

        $paths = $cache->getAllDocPaths();

        self::assertCount(3, $paths);
        self::assertContains('doc1', $paths);
        self::assertContains('doc2', $paths);
        self::assertContains('doc3', $paths);
    }

    public function testLoadInvalidJson(): void
    {
        file_put_contents($this->tempDir . '/_build_meta.json', 'not valid json');

        $cache = new IncrementalBuildCache($this->versioning);

        self::assertFalse($cache->load($this->tempDir));
    }

    public function testLoadInvalidMetadata(): void
    {
        // Write valid JSON but with old cache version
        $data = [
            'metadata' => ['version' => 999], // Invalid version
            'dependencies' => [],
            'outputs' => [],
        ];
        file_put_contents(
            $this->tempDir . '/_build_meta.json',
            json_encode($data, JSON_THROW_ON_ERROR),
        );

        $cache = new IncrementalBuildCache($this->versioning);

        self::assertFalse($cache->load($this->tempDir));
    }

    public function testLoadLegacyMonolithicFormat(): void
    {
        // Simulate legacy format with exports in main file
        $data = [
            'metadata' => $this->versioning->createMetadata(),
            'dependencies' => [],
            'outputs' => [],
            'exports' => [
                'doc1' => [
                    'documentPath' => 'doc1',
                    'contentHash' => str_repeat('a', 32),
                    'exportsHash' => str_repeat('b', 64),
                    'anchors' => [],
                    'sectionTitles' => [],
                    'citations' => [],
                    'lastModified' => 0,
                ],
            ],
        ];
        file_put_contents(
            $this->tempDir . '/_build_meta.json',
            json_encode($data, JSON_THROW_ON_ERROR),
        );

        $cache = new IncrementalBuildCache($this->versioning);
        self::assertTrue($cache->load($this->tempDir));
        self::assertNotNull($cache->getExports('doc1'));
    }

    public function testSetExportsThrowsWhenLimitExceeded(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed maximum');

        $cache = new IncrementalBuildCache($this->versioning);

        // Use reflection to set exports near limit
        $reflection = new ReflectionClass($cache);
        $property = $reflection->getProperty('exports');

        $exports = [];
        for ($i = 0; $i < 100_000; $i++) {
            $exports['doc' . $i] = $this->createExports('doc' . $i);
        }

        $property->setValue($cache, $exports);

        // Try to add one more
        $cache->setExports('one-more', $this->createExports('one-more'));
    }

    public function testSetOutputPathThrowsWhenLimitExceeded(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed maximum');

        $cache = new IncrementalBuildCache($this->versioning);

        // Use reflection to set outputPaths near limit
        $reflection = new ReflectionClass($cache);
        $property = $reflection->getProperty('outputPaths');

        $paths = [];
        for ($i = 0; $i < 100_000; $i++) {
            $paths['doc' . $i] = '/out/doc' . $i . '.html';
        }

        $property->setValue($cache, $paths);

        // Try to add one more
        $cache->setOutputPath('one-more', '/out/one-more.html');
    }

    public function testLoadThrowsOnExcessiveExports(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed maximum');

        // Create legacy format with too many exports
        $exports = [];
        for ($i = 0; $i < 100_001; $i++) {
            $exports['doc' . $i] = [
                'documentPath' => 'doc' . $i,
                'contentHash' => '',
                'exportsHash' => '',
                'anchors' => [],
                'sectionTitles' => [],
                'citations' => [],
                'lastModified' => 0,
            ];
        }

        $data = [
            'metadata' => $this->versioning->createMetadata(),
            'dependencies' => [],
            'outputs' => [],
            'exports' => $exports,
        ];
        file_put_contents(
            $this->tempDir . '/_build_meta.json',
            json_encode($data, JSON_THROW_ON_ERROR),
        );

        $cache = new IncrementalBuildCache($this->versioning);
        $cache->load($this->tempDir);
    }

    public function testMergeStateThrowsOnExcessiveExports(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed maximum');

        $cache = new IncrementalBuildCache($this->versioning);

        // Use reflection to fill cache near limit
        $reflection = new ReflectionClass($cache);
        $property = $reflection->getProperty('exports');

        $exports = [];
        for ($i = 0; $i < 100_000; $i++) {
            $exports['doc' . $i] = $this->createExports('doc' . $i);
        }

        $property->setValue($cache, $exports);

        // Try to merge more
        $state = ['exports' => ['new-doc' => $this->createExports('new-doc')->toArray()]];
        $cache->mergeState($state);
    }

    public function testLoadIgnoresInvalidShardDirectories(): void
    {
        // Create valid export first
        $cache = new IncrementalBuildCache($this->versioning);
        $cache->setExports('doc1', $this->createExports('doc1'));
        $cache->save($this->tempDir);

        // Create invalid shard directory (should be ignored)
        mkdir($this->tempDir . '/_exports/invalid-name', 0o755);
        file_put_contents(
            $this->tempDir . '/_exports/invalid-name/test.json',
            json_encode([
                'path' => 'evil-doc',
                'documentPath' => 'evil-doc',
                'contentHash' => str_repeat('x', 32),
                'exportsHash' => str_repeat('y', 64),
                'anchors' => [],
                'sectionTitles' => [],
                'citations' => [],
                'lastModified' => 0,
            ], JSON_THROW_ON_ERROR),
        );

        // Also create a non-hex shard directory
        mkdir($this->tempDir . '/_exports/ZZ', 0o755);
        file_put_contents(
            $this->tempDir . '/_exports/ZZ/another.json',
            json_encode([
                'path' => 'another-evil',
                'documentPath' => 'another-evil',
                'contentHash' => str_repeat('x', 32),
                'exportsHash' => str_repeat('y', 64),
                'anchors' => [],
                'sectionTitles' => [],
                'citations' => [],
                'lastModified' => 0,
            ], JSON_THROW_ON_ERROR),
        );

        // Load and verify invalid shards were ignored
        $cache2 = new IncrementalBuildCache($this->versioning);
        $cache2->load($this->tempDir);

        self::assertNull($cache2->getExports('evil-doc'));
        self::assertNull($cache2->getExports('another-evil'));
        self::assertNotNull($cache2->getExports('doc1'));
    }

    private function createExports(string $id): DocumentExports
    {
        return new DocumentExports(
            documentPath: $id,
            contentHash: str_repeat('a', 32),
            exportsHash: str_repeat('b', 64),
            anchors: [],
            sectionTitles: [],
            citations: [],
            lastModified: 0,
        );
    }
}
