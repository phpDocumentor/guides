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

use function array_search;
use function array_values;
use function assert;
use function chmod;
use function defined;
use function exec;
use function file_exists;
use function function_exists;
use function is_int;
use function pcntl_signal;
use function pcntl_waitpid;
use function pcntl_wexitstatus;
use function pcntl_wifexited;
use function pcntl_wifsignaled;
use function pcntl_wtermsig;
use function posix_getpid;
use function posix_kill;
use function register_shutdown_function;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function time;
use function unlink;
use function usleep;

use const SIG_DFL;
use const SIGINT;
use const SIGKILL;
use const SIGTERM;
use const WNOHANG;

/**
 * Utility for managing forked child processes with timeouts and cleanup.
 *
 * Provides consistent process management across all parallel processing classes:
 * - Non-blocking wait with configurable timeout
 * - SIGTERM handling for cleanup
 * - Secure temp file creation with proper permissions
 *
 * DESIGN NOTE: This class uses static state intentionally.
 *
 * The static properties ($tempFilesToClean, $shutdownRegistered) are required because:
 * 1. PHP's register_shutdown_function() and pcntl_signal() require a single global handler
 * 2. Temp files must be cleaned up even if the ProcessManager instance goes out of scope
 * 3. Signal handlers must work across all parallel processing code paths
 *
 * This is a standard pattern for cleanup handlers in PHP. The static state is managed
 * carefully:
 * - clearTempFileTracking() must be called in child processes after fork to prevent
 *   children from cleaning up the parent's temp files
 * - cleanupAllTempFiles() is called both on normal shutdown and on SIGTERM/SIGINT
 * - Test environments skip signal handler registration to avoid interference
 */
final class ProcessManager
{
    /** Default timeout in seconds for waiting on child processes */
    public const DEFAULT_TIMEOUT_SECONDS = 300;

    /** Poll interval in microseconds (10ms) */
    private const POLL_INTERVAL_USEC = 10_000;

    /**
     * Temp files to clean on shutdown.
     *
     * Static because shutdown handlers need access regardless of instance scope.
     *
     * @var list<string>
     */
    private static array $tempFilesToClean = [];

    /**
     * Whether shutdown handler is registered.
     *
     * Static to ensure handlers are only registered once per process.
     */
    private static bool $shutdownRegistered = false;

    /**
     * Wait for all child processes with timeout.
     *
     * Uses non-blocking WNOHANG to poll process status, allowing timeout detection.
     * Sends SIGKILL to stuck processes after timeout expires.
     *
     * @param array<int, int> $childPids Map of workerId => pid
     * @param int $timeoutSeconds Maximum time to wait (default 300s)
     *
     * @return array{successes: list<int>, failures: array<int, string>}
     */
    public static function waitForChildrenWithTimeout(
        array $childPids,
        int $timeoutSeconds = self::DEFAULT_TIMEOUT_SECONDS,
    ): array {
        $startTime = time();
        $remaining = $childPids;
        $successes = [];
        $failures = [];

        while ($remaining !== []) {
            foreach ($remaining as $workerId => $pid) {
                $status = 0;
                $result = pcntl_waitpid($pid, $status, WNOHANG);

                if ($result === 0) {
                    // Still running
                    continue;
                }

                if ($result === -1) {
                    // Error - child doesn't exist
                    $failures[$workerId] = 'waitpid failed';
                    unset($remaining[$workerId]);
                    continue;
                }

                // Child exited
                unset($remaining[$workerId]);

                assert(is_int($status));

                if (pcntl_wifexited($status)) {
                    $exitCode = pcntl_wexitstatus($status);
                    if ($exitCode === 0) {
                        $successes[] = $workerId;
                    } else {
                        $failures[$workerId] = sprintf('exit code %d', $exitCode);
                    }
                } elseif (pcntl_wifsignaled($status)) {
                    $signal = pcntl_wtermsig($status);
                    $failures[$workerId] = sprintf('killed by signal %d', $signal);
                }
            }

            // Check timeout
            if (time() - $startTime > $timeoutSeconds) {
                // Kill remaining children
                foreach ($remaining as $workerId => $pid) {
                    self::killProcess($pid);
                    pcntl_waitpid($pid, $status); // Reap zombie
                    $failures[$workerId] = sprintf('killed after %ds timeout', $timeoutSeconds);
                }

                break;
            }

            // Don't spin-wait if processes still running
            if ($remaining === []) {
                continue;
            }

            usleep(self::POLL_INTERVAL_USEC);
        }

        return ['successes' => $successes, 'failures' => $failures];
    }

    /**
     * Create a secure temp file with restricted permissions.
     *
     * Creates temp file with 0600 permissions to prevent other users from reading.
     * Registers file for cleanup on shutdown/signal.
     *
     * @param string $prefix Temp file prefix
     *
     * @return string|false Path to temp file, or false on failure
     */
    public static function createSecureTempFile(string $prefix): string|false
    {
        self::ensureShutdownHandler();

        $tempFile = tempnam(sys_get_temp_dir(), $prefix);
        if ($tempFile === false) {
            return false;
        }

        // Set restrictive permissions (owner read/write only)
        chmod($tempFile, 0o600);

        // Register for cleanup
        self::$tempFilesToClean[] = $tempFile;

        return $tempFile;
    }

    /**
     * Remove a temp file from cleanup list (already cleaned).
     */
    public static function unregisterTempFile(string $tempFile): void
    {
        $key = array_search($tempFile, self::$tempFilesToClean, true);
        if ($key === false) {
            return;
        }

        unset(self::$tempFilesToClean[$key]);
        self::$tempFilesToClean = array_values(self::$tempFilesToClean);
    }

    /**
     * Clean up a temp file and unregister it.
     */
    public static function cleanupTempFile(string $tempFile): void
    {
        @unlink($tempFile);
        self::unregisterTempFile($tempFile);
    }

    /**
     * Clear temp file tracking list.
     *
     * Call this in child processes after fork to prevent them from cleaning up
     * temp files that belong to the parent process when they exit.
     */
    public static function clearTempFileTracking(): void
    {
        self::$tempFilesToClean = [];
    }

    /**
     * Ensure shutdown and signal handlers are registered.
     */
    private static function ensureShutdownHandler(): void
    {
        if (self::$shutdownRegistered) {
            return;
        }

        // Skip handler registration in test environments
        if (defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__')) {
            self::$shutdownRegistered = true;

            return;
        }

        // Register shutdown function for normal termination
        register_shutdown_function([self::class, 'cleanupAllTempFiles']);

        // Register signal handlers if pcntl available
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [self::class, 'handleSignal']);
            pcntl_signal(SIGINT, [self::class, 'handleSignal']);
        }

        self::$shutdownRegistered = true;
    }

    /**
     * Handle termination signals by cleaning up temp files and re-raising signal.
     *
     * Re-raises the signal after cleanup to ensure proper termination status
     * is visible to parent processes and shell (WIFSIGNALED instead of WIFEXITED).
     */
    public static function handleSignal(int $signal): void
    {
        self::cleanupAllTempFiles();

        // Restore default handler and re-raise signal for proper termination
        // This ensures the exit status correctly reflects the signal
        if (function_exists('pcntl_signal')) {
            pcntl_signal($signal, SIG_DFL);
        }

        posix_kill(posix_getpid(), $signal);
    }

    /**
     * Kill a process by PID.
     *
     * Uses posix_kill if available, falls back to shell command otherwise.
     * The PID is always an integer from pcntl_fork, so the shell fallback is safe.
     */
    private static function killProcess(int $pid): void
    {
        if (function_exists('posix_kill')) {
            posix_kill($pid, SIGKILL);

            return;
        }

        // Fallback for systems without posix extension: use shell command
        // Safe because $pid is always an integer from pcntl_fork
        @exec(sprintf('kill -9 %d', $pid));
    }

    /**
     * Clean up all registered temp files.
     */
    public static function cleanupAllTempFiles(): void
    {
        foreach (self::$tempFilesToClean as $file) {
            if (!file_exists($file)) {
                continue;
            }

            @unlink($file);
        }

        self::$tempFilesToClean = [];
    }
}
