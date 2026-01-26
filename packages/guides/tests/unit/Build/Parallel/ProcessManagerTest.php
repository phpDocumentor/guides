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

namespace phpDocumentor\Guides\Build\Parallel;

use PHPUnit\Framework\TestCase;

use function basename;
use function chmod;
use function decoct;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function fileperms;
use function function_exists;
use function pcntl_fork;
use function sleep;
use function time;
use function unlink;

final class ProcessManagerTest extends TestCase
{
    /** @var list<string> Files to clean up after test */
    private array $tempFilesToCleanup = [];

    protected function tearDown(): void
    {
        // Clean up any temp files created during tests
        foreach ($this->tempFilesToCleanup as $file) {
            if (!file_exists($file)) {
                continue;
            }

            @chmod($file, 0o644); // Ensure we can delete it
            @unlink($file);
        }

        ProcessManager::clearTempFileTracking();
    }

    public function testCreateSecureTempFileCreatesFile(): void
    {
        $tempFile = ProcessManager::createSecureTempFile('test_');

        self::assertNotFalse($tempFile);
        self::assertFileExists($tempFile);

        $this->tempFilesToCleanup[] = $tempFile;
    }

    public function testCreateSecureTempFileHasRestrictedPermissions(): void
    {
        $tempFile = ProcessManager::createSecureTempFile('perm_test_');

        self::assertNotFalse($tempFile);
        $this->tempFilesToCleanup[] = $tempFile;

        // Get permissions (last 3 octal digits)
        $perms = fileperms($tempFile) & 0o777;

        // Should be 0600 (owner read/write only)
        self::assertSame(
            '600',
            decoct($perms),
            'Temp file should have 0600 permissions (got ' . decoct($perms) . ')',
        );
    }

    public function testCreateSecureTempFileWithCustomPrefix(): void
    {
        $tempFile = ProcessManager::createSecureTempFile('custom_prefix_');

        self::assertNotFalse($tempFile);
        $this->tempFilesToCleanup[] = $tempFile;

        // Verify the prefix is part of the filename
        $filename = basename($tempFile);
        self::assertStringStartsWith('custom_prefix_', $filename);
    }

    public function testCleanupTempFileDeletesFile(): void
    {
        $tempFile = ProcessManager::createSecureTempFile('cleanup_test_');

        self::assertNotFalse($tempFile);
        self::assertFileExists($tempFile);

        ProcessManager::cleanupTempFile($tempFile);

        self::assertFileDoesNotExist($tempFile);
    }

    public function testCleanupTempFileHandlesNonexistentFile(): void
    {
        // Should not throw even if file doesn't exist
        ProcessManager::cleanupTempFile('/nonexistent/path/to/file');

        // If we get here, no exception was thrown
        self::assertTrue(true);
    }

    public function testClearTempFileTrackingPreventsCleanup(): void
    {
        $tempFile = ProcessManager::createSecureTempFile('clear_test_');

        self::assertNotFalse($tempFile);
        $this->tempFilesToCleanup[] = $tempFile;

        // Clear tracking (simulating child process behavior)
        ProcessManager::clearTempFileTracking();

        // cleanupAllTempFiles should not delete the file now
        ProcessManager::cleanupAllTempFiles();

        // File should still exist because tracking was cleared
        self::assertFileExists($tempFile);
    }

    public function testUnregisterTempFile(): void
    {
        $tempFile = ProcessManager::createSecureTempFile('unregister_test_');

        self::assertNotFalse($tempFile);
        $this->tempFilesToCleanup[] = $tempFile;

        // Unregister without deleting
        ProcessManager::unregisterTempFile($tempFile);

        // cleanupAllTempFiles should not affect this file
        ProcessManager::cleanupAllTempFiles();

        // File should still exist
        self::assertFileExists($tempFile);
    }

    public function testMultipleTempFilesCreatedAndCleaned(): void
    {
        $files = [];
        for ($i = 0; $i < 5; $i++) {
            $tempFile = ProcessManager::createSecureTempFile('multi_test_');
            self::assertNotFalse($tempFile);
            $files[] = $tempFile;
            $this->tempFilesToCleanup[] = $tempFile;
        }

        // Verify all exist
        foreach ($files as $file) {
            self::assertFileExists($file);
        }

        // Clean up all
        ProcessManager::cleanupAllTempFiles();

        // Verify all removed
        foreach ($files as $file) {
            self::assertFileDoesNotExist($file);
        }
    }

    public function testTempFileIsWritable(): void
    {
        $tempFile = ProcessManager::createSecureTempFile('write_test_');

        self::assertNotFalse($tempFile);
        $this->tempFilesToCleanup[] = $tempFile;

        // Write content
        $content = 'test content ' . time();
        file_put_contents($tempFile, $content);

        // Verify content
        self::assertSame($content, file_get_contents($tempFile));
    }

    /**
     * @requires extension pcntl
     * @group integration
     */
    public function testWaitForChildrenWithSuccessfulExit(): void
    {
        if (!function_exists('pcntl_fork')) {
            self::markTestSkipped('pcntl extension not available');
        }

        $pid = pcntl_fork();

        if ($pid === -1) {
            self::markTestSkipped('Unable to fork');
        }

        if ($pid === 0) {
            // Child: exit immediately with success
            exit(0);
        }

        // Parent: wait for child
        $result = ProcessManager::waitForChildrenWithTimeout([0 => $pid], 5);

        self::assertContains(0, $result['successes']);
        self::assertEmpty($result['failures']);
    }

    /**
     * @requires extension pcntl
     * @group integration
     */
    public function testWaitForChildrenWithFailedExit(): void
    {
        if (!function_exists('pcntl_fork')) {
            self::markTestSkipped('pcntl extension not available');
        }

        $pid = pcntl_fork();

        if ($pid === -1) {
            self::markTestSkipped('Unable to fork');
        }

        if ($pid === 0) {
            // Child: exit with error code
            exit(42);
        }

        // Parent: wait for child
        $result = ProcessManager::waitForChildrenWithTimeout([0 => $pid], 5);

        self::assertEmpty($result['successes']);
        self::assertArrayHasKey(0, $result['failures']);
        self::assertStringContainsString('exit code 42', $result['failures'][0]);
    }

    /**
     * @requires extension pcntl
     * @group integration
     */
    public function testWaitForChildrenWithMultipleChildren(): void
    {
        if (!function_exists('pcntl_fork')) {
            self::markTestSkipped('pcntl extension not available');
        }

        $childPids = [];

        for ($i = 0; $i < 3; $i++) {
            $pid = pcntl_fork();

            if ($pid === -1) {
                self::markTestSkipped('Unable to fork');
            }

            if ($pid === 0) {
                // Child: exit with success
                ProcessManager::clearTempFileTracking(); // Don't interfere with parent's temp files
                exit(0);
            }

            $childPids[$i] = $pid;
        }

        // Parent: wait for all children
        $result = ProcessManager::waitForChildrenWithTimeout($childPids, 10);

        self::assertCount(3, $result['successes']);
        self::assertEmpty($result['failures']);
    }

    /**
     * @requires extension pcntl
     * @group integration
     */
    public function testWaitForChildrenWithTimeout(): void
    {
        if (!function_exists('pcntl_fork')) {
            self::markTestSkipped('pcntl extension not available');
        }

        $pid = pcntl_fork();

        if ($pid === -1) {
            self::markTestSkipped('Unable to fork');
        }

        if ($pid === 0) {
            // Child: sleep longer than timeout
            ProcessManager::clearTempFileTracking();
            sleep(60);
            exit(0);
        }

        // Parent: wait with short timeout
        $result = ProcessManager::waitForChildrenWithTimeout([0 => $pid], 1);

        // Child should have been killed
        self::assertEmpty($result['successes']);
        self::assertArrayHasKey(0, $result['failures']);
        self::assertStringContainsString('timeout', $result['failures'][0]);
    }

    public function testDefaultTimeoutConstant(): void
    {
        // Verify the constant exists and has a reasonable value
        self::assertSame(300, ProcessManager::DEFAULT_TIMEOUT_SECONDS);
    }
}
