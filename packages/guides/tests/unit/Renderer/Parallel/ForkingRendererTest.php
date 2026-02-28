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

namespace phpDocumentor\Guides\Renderer\Parallel;

use League\Tactician\CommandBus;
use phpDocumentor\Guides\Build\Parallel\ParallelSettings;
use PHPUnit\Framework\TestCase;

final class ForkingRendererTest extends TestCase
{
    private CommandBus $commandBus;
    private DocumentNavigationProvider $navigationProvider;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(CommandBus::class);
        $this->navigationProvider = new DocumentNavigationProvider();
    }

    public function testParallelCanBeDisabledViaSettings(): void
    {
        $settings = new ParallelSettings();
        $settings->setWorkerCount(-1); // -1 disables parallel

        $renderer = new ForkingRenderer(
            $this->commandBus,
            $this->navigationProvider,
            null,
            $settings,
        );

        // ParallelSettings with workerCount=-1 should disable parallel rendering
        self::assertFalse($settings->isEnabled());
        self::assertFalse($renderer->isParallelEnabled());
    }

    public function testWorkerCountFromSettings(): void
    {
        $settings = new ParallelSettings();
        $settings->setWorkerCount(4);

        $renderer = new ForkingRenderer(
            $this->commandBus,
            $this->navigationProvider,
            null,
            $settings,
        );

        self::assertTrue($renderer->isParallelEnabled());
        // Worker count from settings should be used
        self::assertSame(4, $renderer->getWorkerCount());
    }

    public function testSetWorkerCountBoundsValues(): void
    {
        $renderer = new ForkingRenderer(
            $this->commandBus,
            $this->navigationProvider,
        );

        // Test minimum bound
        $renderer->setWorkerCount(0);
        self::assertSame(1, $renderer->getWorkerCount(), 'Should bound to minimum of 1');

        // Test maximum bound
        $renderer->setWorkerCount(100);
        self::assertSame(16, $renderer->getWorkerCount(), 'Should bound to maximum of 16');

        // Test normal value
        $renderer->setWorkerCount(4);
        self::assertSame(4, $renderer->getWorkerCount());
    }

    public function testSetParallelEnabled(): void
    {
        $renderer = new ForkingRenderer(
            $this->commandBus,
            $this->navigationProvider,
        );

        $renderer->setParallelEnabled(false);
        self::assertFalse($renderer->isParallelEnabled());

        $renderer->setParallelEnabled(true);
        self::assertTrue($renderer->isParallelEnabled());
    }

    public function testInitialCountersAreZero(): void
    {
        $renderer = new ForkingRenderer(
            $this->commandBus,
            $this->navigationProvider,
        );

        self::assertSame(0, $renderer->getTotalRendered());
        self::assertSame(0, $renderer->getSkippedCount());
    }

    public function testWorkerCountFromParallelSettings(): void
    {
        $settings = new ParallelSettings();
        $settings->setWorkerCount(8);

        $renderer = new ForkingRenderer(
            $this->commandBus,
            $this->navigationProvider,
            null,
            $settings,
        );

        // With explicit worker count in settings, it should use that
        self::assertSame(8, $renderer->getWorkerCount());
    }

    public function testWorkerCountAutoWithParallelSettings(): void
    {
        // When worker count is 'auto' (0), it should auto-detect
        $settings = new ParallelSettings();
        $settings->setWorkerCount(0); // 0 means auto-detect

        $renderer = new ForkingRenderer(
            $this->commandBus,
            $this->navigationProvider,
            null,
            $settings,
        );

        // Auto-detection should return at least 1
        self::assertGreaterThanOrEqual(1, $renderer->getWorkerCount());
    }
}
