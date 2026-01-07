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

namespace phpDocumentor\Guides\Graphs\Renderer;

use FilesystemIterator;
use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

use function assert;
use function basename;
use function is_dir;
use function realpath;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

final class PlantumlRendererTest extends TestCase
{
    private const TEMP_DIR_PREFIX = 'plantuml-test-';

    public function testRenderCreatesTempDirectoryWhenMissing(): void
    {
        $tempDir = sys_get_temp_dir() . '/' . self::TEMP_DIR_PREFIX . uniqid('', true);

        self::assertDirectoryDoesNotExist($tempDir);

        $renderer = new PlantumlRenderer(new NullLogger(), '/non/existent/plantuml', $tempDir);

        $renderContext = $this->createMock(RenderContext::class);
        $renderContext->method('getLoggerInformation')->willReturn([]);

        try {
            // Render will fail (returns null) or throw due to non-existent plantuml binary.
            // We only care about verifying that the temp directory was created.
            $renderer->render($renderContext, 'A -> B');
        } catch (Throwable) {
            // Expected: plantuml binary doesn't exist
        }

        self::assertDirectoryExists($tempDir, 'Temp directory should have been created');

        $this->removeTempDirSafely($tempDir);
    }

    private function removeTempDirSafely(string $dir): void
    {
        if ($dir === '' || !is_dir($dir)) {
            return;
        }

        $realDir = realpath($dir);
        if ($realDir === false) {
            return;
        }

        $realSysTmp = realpath(sys_get_temp_dir());
        if ($realSysTmp === false) {
            return;
        }

        // Safety: must be under system temp and have our prefix
        self::assertStringStartsWith(
            $realSysTmp . DIRECTORY_SEPARATOR,
            $realDir . DIRECTORY_SEPARATOR,
            'Refusing to delete directory outside system temp',
        );
        self::assertStringContainsString(
            self::TEMP_DIR_PREFIX,
            basename($realDir),
            'Refusing to delete directory without expected prefix',
        );

        /** @var RecursiveIteratorIterator<RecursiveDirectoryIterator> $iterator */
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($realDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $file) {
            assert($file instanceof SplFileInfo);
            $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
        }

        @rmdir($realDir);
    }
}
