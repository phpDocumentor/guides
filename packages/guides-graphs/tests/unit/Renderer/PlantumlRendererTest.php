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

use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

use function rmdir;
use function sys_get_temp_dir;
use function uniqid;

final class PlantumlRendererTest extends TestCase
{
    public function testRenderCreatesTempDirectoryWhenMissing(): void
    {
        $tempDir = sys_get_temp_dir() . '/plantuml-test-' . uniqid();

        // Ensure the directory does not exist
        self::assertDirectoryDoesNotExist($tempDir);

        // Use a non-existent binary path - the render will fail but directory should be created first
        $renderer = new PlantumlRenderer(new NullLogger(), '/non/existent/plantuml', $tempDir);

        $renderContext = $this->createMock(RenderContext::class);
        $renderContext->method('getLoggerInformation')->willReturn([]);

        // The render will fail due to missing binary, but the temp directory should be created
        $renderer->render($renderContext, 'A -> B');

        self::assertDirectoryExists($tempDir);

        // Clean up
        @rmdir($tempDir);
    }
}
