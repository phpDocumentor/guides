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

use function clearstatcache;
use function file_exists;
use function file_put_contents;
use function filemtime;
use function is_dir;
use function is_link;
use function mkdir;
use function rmdir;
use function symlink;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

final class ChangeDetectorTest extends TestCase
{
    private ChangeDetector $detector;
    private ContentHasher $hasher;
    private string $tempDir;
    /** @var string[] */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        $this->hasher = new ContentHasher();
        $this->detector = new ChangeDetector($this->hasher);
        $this->tempDir = sys_get_temp_dir() . '/change-detector-test-' . uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (!file_exists($file) && !is_link($file)) {
                continue;
            }

            unlink($file);
        }

        if (!is_dir($this->tempDir)) {
            return;
        }

        rmdir($this->tempDir);
    }

    private function createTempFile(string $name, string $content): string
    {
        $path = $this->tempDir . '/' . $name;
        file_put_contents($path, $content);
        $this->tempFiles[] = $path;

        return $path;
    }

    public function testDetectsNewFiles(): void
    {
        $result = $this->detector->detectChanges(
            ['doc1', 'doc2'],
            [], // No cached exports = all files are new
        );

        self::assertSame(['doc1', 'doc2'], $result->new);
        self::assertSame([], $result->dirty);
        self::assertSame([], $result->clean);
        self::assertSame([], $result->deleted);
    }

    public function testDetectsDeletedFiles(): void
    {
        $cachedExports = [
            'existing' => $this->createExports('existing', 'hash1', 100),
            'deleted' => $this->createExports('deleted', 'hash2', 100),
        ];

        $result = $this->detector->detectChanges(
            ['existing'], // Only 'existing' is present
            $cachedExports,
        );

        self::assertSame(['deleted'], $result->deleted);
    }

    public function testDetectsChangedContentWithHashMismatch(): void
    {
        $filePath = $this->createTempFile('doc.rst', 'new content');
        clearstatcache(true, $filePath);
        $currentMtime = (int) filemtime($filePath);

        // Cache has different content hash but same mtime
        $cachedExports = [
            $filePath => $this->createExports($filePath, 'old-hash', $currentMtime + 1),
        ];

        $result = $this->detector->detectChanges([$filePath], $cachedExports);

        self::assertSame([$filePath], $result->dirty);
        self::assertSame([], $result->clean);
    }

    public function testFastPathWhenMtimeUnchanged(): void
    {
        $filePath = $this->createTempFile('unchanged.rst', 'content');
        clearstatcache(true, $filePath);
        $mtime = (int) filemtime($filePath);
        $hash = $this->hasher->hashFile($filePath);

        $cachedExports = [
            $filePath => $this->createExports($filePath, $hash, $mtime),
        ];

        $result = $this->detector->detectChanges([$filePath], $cachedExports);

        self::assertSame([], $result->dirty);
        self::assertSame([$filePath], $result->clean);

        // Verify fast path was used
        $stats = $this->detector->getStats();
        self::assertSame(1, $stats['fastPathHits']);
        self::assertSame(0, $stats['hashComputations']);
    }

    public function testMtimeChangeWithSameContentIsClean(): void
    {
        $filePath = $this->createTempFile('touched.rst', 'same content');
        clearstatcache(true, $filePath);
        $hash = $this->hasher->hashFile($filePath);
        $oldMtime = (int) filemtime($filePath) - 100; // Pretend old mtime was different

        $cachedExports = [
            $filePath => $this->createExports($filePath, $hash, $oldMtime),
        ];

        $result = $this->detector->detectChanges([$filePath], $cachedExports);

        // Should be clean because content hash matches
        self::assertSame([], $result->dirty);
        self::assertSame([$filePath], $result->clean);

        // Hash computation was needed because mtime changed
        $stats = $this->detector->getStats();
        self::assertSame(0, $stats['fastPathHits']);
        self::assertSame(1, $stats['hashComputations']);
    }

    public function testHasFileChangedReturnsTrueForNewFile(): void
    {
        self::assertTrue($this->detector->hasFileChanged('/some/path', null));
    }

    public function testHasFileChangedReturnsFalseWhenMtimeMatches(): void
    {
        $filePath = $this->createTempFile('check.rst', 'content');
        clearstatcache(true, $filePath);
        $mtime = (int) filemtime($filePath);
        $hash = $this->hasher->hashFile($filePath);

        $cached = $this->createExports($filePath, $hash, $mtime);

        self::assertFalse($this->detector->hasFileChanged($filePath, $cached));
    }

    public function testHasFileChangedReturnsTrueWhenContentDiffers(): void
    {
        $filePath = $this->createTempFile('changed.rst', 'new content');
        clearstatcache(true, $filePath);
        $mtime = (int) filemtime($filePath);

        // Cached has different hash
        $cached = $this->createExports($filePath, 'different-hash', $mtime + 1);

        self::assertTrue($this->detector->hasFileChanged($filePath, $cached));
    }

    public function testGetFileMtimeReturnsZeroForNonexistent(): void
    {
        self::assertSame(0, $this->detector->getFileMtime('/nonexistent/file.rst'));
    }

    public function testGetFileMtimeReturnsActualMtime(): void
    {
        $filePath = $this->createTempFile('mtime.rst', 'content');
        clearstatcache(true, $filePath);
        $expected = (int) filemtime($filePath);

        self::assertSame($expected, $this->detector->getFileMtime($filePath));
    }

    public function testDetectChangesWithResolverUsesCustomResolver(): void
    {
        $filePath = $this->createTempFile('resolved.rst', 'content');

        $result = $this->detector->detectChangesWithResolver(
            ['doc-path'],
            [],
            static fn (string $docPath) => $filePath, // Custom resolver
        );

        self::assertSame(['doc-path'], $result->new);
    }

    public function testStatsResetBetweenDetectionRuns(): void
    {
        $file1 = $this->createTempFile('file1.rst', 'content1');
        $file2 = $this->createTempFile('file2.rst', 'content2');
        clearstatcache(true);

        // First run with two files
        $this->detector->detectChanges([$file1, $file2], []);

        // Second run with one file
        $this->detector->detectChanges([$file1], []);

        $stats = $this->detector->getStats();
        // Stats should reflect only the last run
        self::assertSame(0, $stats['fastPathHits']);
        self::assertSame(0, $stats['hashComputations']);
    }

    public function testEmptyFileIsDetectedCorrectly(): void
    {
        $filePath = $this->createTempFile('empty.rst', '');
        clearstatcache(true, $filePath);
        $hash = $this->hasher->hashFile($filePath);
        $mtime = (int) filemtime($filePath);

        $cachedExports = [
            $filePath => $this->createExports($filePath, $hash, $mtime),
        ];

        $result = $this->detector->detectChanges([$filePath], $cachedExports);

        // Empty file should be clean if hash matches
        self::assertSame([], $result->dirty);
        self::assertSame([$filePath], $result->clean);
    }

    public function testZeroMtimeForcesHashComputation(): void
    {
        $filePath = $this->createTempFile('zero-mtime.rst', 'content');
        clearstatcache(true, $filePath);
        $hash = $this->hasher->hashFile($filePath);

        // Cache with zero mtime should force hash computation
        $cachedExports = [
            $filePath => $this->createExports($filePath, $hash, 0),
        ];

        $result = $this->detector->detectChanges([$filePath], $cachedExports);

        // Should be clean because content hash matches
        self::assertSame([], $result->dirty);
        self::assertSame([$filePath], $result->clean);

        // Hash was computed because cached mtime was 0
        $stats = $this->detector->getStats();
        self::assertSame(0, $stats['fastPathHits']);
        self::assertSame(1, $stats['hashComputations']);
    }

    public function testResolverReturnsNonExistentPath(): void
    {
        $cachedExports = [
            'doc-path' => $this->createExports('doc-path', 'old-hash', 12_345),
        ];

        $result = $this->detector->detectChangesWithResolver(
            ['doc-path'],
            $cachedExports,
            static fn (string $docPath) => '/nonexistent/path/' . $docPath . '.rst',
        );

        // Non-existent file should be detected as dirty (mtime=0 differs from cache)
        self::assertSame(['doc-path'], $result->dirty);
        self::assertSame([], $result->clean);
    }

    public function testMixedChangesInSingleRun(): void
    {
        // Set up files
        $cleanFile = $this->createTempFile('clean.rst', 'clean content');
        $dirtyFile = $this->createTempFile('dirty.rst', 'new dirty content');
        clearstatcache(true);

        $cleanMtime = (int) filemtime($cleanFile);
        $cleanHash = $this->hasher->hashFile($cleanFile);

        $cachedExports = [
            $cleanFile => $this->createExports($cleanFile, $cleanHash, $cleanMtime),
            $dirtyFile => $this->createExports($dirtyFile, 'old-hash', (int) filemtime($dirtyFile) + 1),
            'deleted-file' => $this->createExports('deleted-file', 'hash', 12_345),
        ];

        $result = $this->detector->detectChanges(
            [$cleanFile, $dirtyFile, 'new-file'],
            $cachedExports,
        );

        self::assertSame([$dirtyFile], $result->dirty);
        self::assertSame([$cleanFile], $result->clean);
        self::assertSame(['new-file'], $result->new);
        self::assertSame(['deleted-file'], $result->deleted);
    }

    public function testSymlinkTargetIsHashed(): void
    {
        $targetFile = $this->createTempFile('target.rst', 'symlink content');
        $symlinkPath = $this->tempDir . '/symlink.rst';

        // Create symlink if possible
        if (!@symlink($targetFile, $symlinkPath)) {
            self::markTestSkipped('Symlinks not supported on this system');
        }

        $this->tempFiles[] = $symlinkPath;
        clearstatcache(true, $symlinkPath);
        clearstatcache(true, $targetFile);

        // Verify symlink was created
        self::assertTrue(is_link($symlinkPath));

        $mtime = (int) filemtime($symlinkPath);
        $hash = $this->hasher->hashFile($symlinkPath);

        $cachedExports = [
            $symlinkPath => $this->createExports($symlinkPath, $hash, $mtime),
        ];

        $result = $this->detector->detectChanges([$symlinkPath], $cachedExports);

        self::assertSame([], $result->dirty);
        self::assertSame([$symlinkPath], $result->clean);
    }

    public function testFileDeletedBetweenMtimeAndHashCheck(): void
    {
        // This tests behavior when mtime check passes but hash fails due to deletion
        // In practice, this is a race condition edge case
        $filePath = '/nonexistent/deleted-during-check.rst';

        $cachedExports = [
            $filePath => $this->createExports($filePath, 'old-hash', 0), // mtime=0 forces hash check
        ];

        $result = $this->detector->detectChanges([$filePath], $cachedExports);

        // File doesn't exist, hash will be empty, different from 'old-hash' -> dirty
        self::assertSame([$filePath], $result->dirty);
    }

    public function testHasFileChangedWithZeroMtimeCache(): void
    {
        $filePath = $this->createTempFile('has-changed.rst', 'content');
        clearstatcache(true, $filePath);
        $hash = $this->hasher->hashFile($filePath);

        // Cached mtime is 0, should force hash comparison
        $cached = $this->createExports($filePath, $hash, 0);

        // Content same, so should report unchanged
        self::assertFalse($this->detector->hasFileChanged($filePath, $cached));
    }

    public function testHasFileChangedWithZeroMtimeCacheAndDifferentHash(): void
    {
        $filePath = $this->createTempFile('has-changed2.rst', 'new content');
        clearstatcache(true, $filePath);

        // Cached mtime is 0, different hash
        $cached = $this->createExports($filePath, 'old-hash', 0);

        self::assertTrue($this->detector->hasFileChanged($filePath, $cached));
    }

    private function createExports(string $path, string $contentHash, int $mtime): DocumentExports
    {
        return new DocumentExports(
            documentPath: $path,
            contentHash: $contentHash,
            exportsHash: 'exports-hash',
            anchors: [],
            sectionTitles: [],
            citations: [],
            lastModified: $mtime,
            documentTitle: 'Test',
        );
    }
}
