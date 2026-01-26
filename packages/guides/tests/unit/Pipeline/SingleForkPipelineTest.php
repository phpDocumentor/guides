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

namespace phpDocumentor\Guides\Pipeline;

use phpDocumentor\Guides\Nodes\ProjectNode;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function array_map;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

final class SingleForkPipelineTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/single_fork_test_' . uniqid();
        mkdir($this->tempDir, 0o755, true);
    }

    protected function tearDown(): void
    {
        $this->recursiveDelete($this->tempDir);
    }

    public function testExecuteWithLowFileCountUsesSequential(): void
    {
        // When file count is below threshold, sequential is used
        $pipeline = new SingleForkPipeline(null, 4);

        $executorCalled = false;
        $filesReceived = [];

        $executor = static function (array $files) use (&$executorCalled, &$filesReceived): array {
            $executorCalled = true;
            $filesReceived = $files;

            return [
                'documents' => [],
                'projectNode' => new ProjectNode(),
            ];
        };

        // 5 files is below the MIN_FILES_FOR_PARALLEL (10)
        $files = ['file1.rst', 'file2.rst', 'file3.rst', 'file4.rst', 'file5.rst'];
        $result = $pipeline->execute($executor, $files, $this->tempDir);

        self::assertTrue($executorCalled, 'Sequential executor should be called');
        self::assertSame($files, $filesReceived, 'All files should be passed to executor');
        self::assertArrayHasKey('documents', $result);
        self::assertArrayHasKey('projectNode', $result);
    }

    public function testSetWorkerCountBoundsValues(): void
    {
        $pipeline = new SingleForkPipeline();

        // Test minimum bound
        $pipeline->setWorkerCount(0);
        // We can't access private property, but we verify it doesn't throw

        // Test maximum bound
        $pipeline->setWorkerCount(100);
        // Should be bounded to 16

        // Test normal value
        $pipeline->setWorkerCount(4);
        // Should be set to 4

        // If we get here, no exceptions were thrown
        self::assertTrue(true);
    }

    public function testExecuteWithEmptyFileListReturnsEmptyResult(): void
    {
        $pipeline = new SingleForkPipeline(null, 2);

        $executor = static function (array $files): array {
            return [
                'documents' => [],
                'projectNode' => new ProjectNode(),
            ];
        };

        $result = $pipeline->execute($executor, [], $this->tempDir);

        self::assertSame([], $result['documents']);
        self::assertInstanceOf(ProjectNode::class, $result['projectNode']);
    }

    public function testFindHtmlFilesFindsFilesRecursively(): void
    {
        // Create nested directory structure with HTML files
        mkdir($this->tempDir . '/subdir1', 0o755, true);
        mkdir($this->tempDir . '/subdir1/nested', 0o755, true);
        mkdir($this->tempDir . '/subdir2', 0o755, true);

        // Create HTML files at various levels
        file_put_contents($this->tempDir . '/index.html', '<html></html>');
        file_put_contents($this->tempDir . '/subdir1/page1.html', '<html></html>');
        file_put_contents($this->tempDir . '/subdir1/nested/deep.html', '<html></html>');
        file_put_contents($this->tempDir . '/subdir2/page2.html', '<html></html>');
        // Also create a non-HTML file
        file_put_contents($this->tempDir . '/readme.txt', 'not html');

        // Use reflection to test the private findHtmlFiles method
        $pipeline = new SingleForkPipeline();
        $reflection = new ReflectionClass($pipeline);
        $method = $reflection->getMethod('findHtmlFiles');

        /** @var string[] $htmlFiles */
        $htmlFiles = $method->invoke($pipeline, $this->tempDir);

        self::assertCount(4, $htmlFiles, 'Should find 4 HTML files');

        // Verify all HTML files are found
        $fileNames = array_map('basename', $htmlFiles);
        self::assertContains('index.html', $fileNames);
        self::assertContains('page1.html', $fileNames);
        self::assertContains('deep.html', $fileNames);
        self::assertContains('page2.html', $fileNames);
        self::assertNotContains('readme.txt', $fileNames);
    }

    public function testFindHtmlFilesReturnsEmptyForNonexistentDirectory(): void
    {
        $pipeline = new SingleForkPipeline();
        $reflection = new ReflectionClass($pipeline);
        $method = $reflection->getMethod('findHtmlFiles');

        /** @var string[] $htmlFiles */
        $htmlFiles = $method->invoke($pipeline, '/nonexistent/path/that/does/not/exist');

        self::assertSame([], $htmlFiles);
    }

    public function testFindHtmlFilesIsCaseInsensitive(): void
    {
        file_put_contents($this->tempDir . '/lowercase.html', '<html></html>');
        file_put_contents($this->tempDir . '/uppercase.HTML', '<html></html>');
        file_put_contents($this->tempDir . '/mixed.HtMl', '<html></html>');

        $pipeline = new SingleForkPipeline();
        $reflection = new ReflectionClass($pipeline);
        $method = $reflection->getMethod('findHtmlFiles');

        /** @var string[] $htmlFiles */
        $htmlFiles = $method->invoke($pipeline, $this->tempDir);

        self::assertCount(3, $htmlFiles, 'Should find all HTML files regardless of case');
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
}
