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

namespace phpDocumentor\Guides\Compiler\Parallel;

/**
 * Interface for compilation cache that supports parallel processing.
 *
 * Implementations allow state extraction from child processes and
 * merging back into the parent process during parallel compilation.
 */
interface CompilationCacheInterface
{
    /**
     * Extract cache state for serialization to parent process.
     *
     * @return array<string, mixed> Serializable cache state
     */
    public function extractState(): array;

    /**
     * Merge cache state from a child process.
     *
     * @param array<string, mixed> $state State extracted from child process
     */
    public function mergeState(array $state): void;

    /**
     * Get all document exports for logging/debugging.
     *
     * @return array<string, mixed>
     */
    public function getAllExports(): array;
}
