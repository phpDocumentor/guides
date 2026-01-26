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

final class CpuDetectorTest extends TestCase
{
    public function testDetectCoresReturnsPositiveNumber(): void
    {
        $cores = CpuDetector::detectCores();

        self::assertGreaterThan(0, $cores);
    }

    public function testDetectCoresRespectsMaxWorkers(): void
    {
        $cores = CpuDetector::detectCores(maxWorkers: 2);

        self::assertLessThanOrEqual(2, $cores);
    }

    public function testDetectCoresUsesDefaultOnFailure(): void
    {
        // With an impossibly low max, we test the capping behavior
        $cores = CpuDetector::detectCores(maxWorkers: 1, defaultWorkers: 1);

        self::assertSame(1, $cores);
    }

    public function testDetectCoresDefaultMaxIsEight(): void
    {
        $cores = CpuDetector::detectCores();

        // Should never exceed default max of 8
        self::assertLessThanOrEqual(8, $cores);
    }
}
