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

use function file_get_contents;
use function is_file;
use function min;
use function shell_exec;
use function substr_count;
use function trim;

/**
 * Utility for detecting CPU core count for parallel processing.
 *
 * Provides cross-platform detection of available CPU cores for
 * configuring parallel worker counts.
 */
final class CpuDetector
{
    /**
     * Detect the number of CPU cores available.
     *
     * @param int $maxWorkers Maximum number of workers to return (default 8)
     * @param int $defaultWorkers Default if detection fails (default 4)
     *
     * @return int Number of CPU cores (capped at $maxWorkers)
     */
    public static function detectCores(int $maxWorkers = 8, int $defaultWorkers = 4): int
    {
        // Try /proc/cpuinfo on Linux
        if (is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            if ($cpuinfo !== false) {
                $count = substr_count($cpuinfo, 'processor');
                if ($count > 0) {
                    return min($count, $maxWorkers);
                }
            }
        }

        // Try nproc command (Linux/GNU)
        $nproc = @shell_exec('nproc 2>/dev/null');
        if ($nproc !== null && $nproc !== false) {
            $count = (int) trim($nproc);
            if ($count > 0) {
                return min($count, $maxWorkers);
            }
        }

        // Try sysctl on macOS/BSD
        $sysctl = @shell_exec('sysctl -n hw.ncpu 2>/dev/null');
        if ($sysctl !== null && $sysctl !== false) {
            $count = (int) trim($sysctl);
            if ($count > 0) {
                return min($count, $maxWorkers);
            }
        }

        return $defaultWorkers;
    }
}
