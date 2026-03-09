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

use InvalidArgumentException;

use function array_merge;
use function count;
use function is_string;
use function sort;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strlen;

/**
 * Detects changes that require a full rebuild of all documents.
 *
 * Certain changes affect all pages:
 * - Toctree structure changes (navigation)
 * - Theme or template changes
 * - Global configuration changes
 * - Interlink inventory changes
 */
final class GlobalInvalidationDetector
{
    /**
     * Default files/patterns that trigger full rebuild when changed.
     */
    private const DEFAULT_PATTERNS = [
        // Configuration files
        'guides.xml',
        'Settings.cfg',
        'conf.py',
        // Theme files (directory patterns end with /)
        '_static/',
        '_templates/',
        // Interlink files
        'objects.inv',
    ];

    /** @var string[] */
    private readonly array $globalPatterns;

    /** Maximum pattern length to prevent DoS via regex processing */
    private const MAX_PATTERN_LENGTH = 256;

    /**
     * @param string[] $globalPatterns Files/patterns that trigger full rebuild.
     *                                 Directory patterns should end with '/'.
     *                                 If empty, uses DEFAULT_PATTERNS.
     *
     * @throws InvalidArgumentException If any pattern is invalid
     */
    public function __construct(
        array $globalPatterns = [],
    ) {
        if ($globalPatterns !== []) {
            foreach ($globalPatterns as $pattern) {
                if (!is_string($pattern)) {
                    throw new InvalidArgumentException('Global pattern must be a string');
                }

                if ($pattern === '') {
                    throw new InvalidArgumentException('Global pattern cannot be empty');
                }

                if (strlen($pattern) > self::MAX_PATTERN_LENGTH) {
                    throw new InvalidArgumentException(
                        'Global pattern exceeds maximum length of ' . self::MAX_PATTERN_LENGTH,
                    );
                }
            }

            $this->globalPatterns = $globalPatterns;
        } else {
            $this->globalPatterns = self::DEFAULT_PATTERNS;
        }
    }

    /**
     * Check if any changes require a full rebuild.
     *
     * @param ChangeDetectionResult $changes Detected changes
     * @param string|null $settingsHash Current settings hash
     * @param string|null $cachedSettingsHash Previous settings hash
     *
     * @return bool True if full rebuild is required
     */
    public function requiresFullRebuild(
        ChangeDetectionResult $changes,
        string|null $settingsHash = null,
        string|null $cachedSettingsHash = null,
    ): bool {
        // Check if settings changed
        if ($settingsHash !== null && $cachedSettingsHash !== null) {
            if ($settingsHash !== $cachedSettingsHash) {
                return true;
            }
        }

        // Check if any global files changed
        $allChangedFiles = array_merge($changes->dirty, $changes->new, $changes->deleted);

        foreach ($allChangedFiles as $file) {
            if ($this->isGlobalFile($file)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a file is a global file that affects all documents.
     */
    public function isGlobalFile(string $filePath): bool
    {
        $normalizedPath = str_replace('\\', '/', $filePath);

        foreach ($this->globalPatterns as $pattern) {
            if (str_ends_with($pattern, '/')) {
                // Directory pattern - must match as complete path segment
                // e.g., pattern "foo/" matches "/path/foo/bar.txt" but not "/path/prefix_foo/bar.txt"
                if (
                    str_starts_with($normalizedPath, $pattern) ||
                    str_contains($normalizedPath, '/' . $pattern)
                ) {
                    return true;
                }
            } else {
                // File pattern
                if (str_ends_with($normalizedPath, '/' . $pattern) || $normalizedPath === $pattern) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if toctree structure changed.
     * This is detected by comparing the document hierarchy.
     *
     * @param array<string, string[]> $oldToctree Previous toctree structure
     * @param array<string, string[]> $newToctree Current toctree structure
     *
     * @return bool True if structure changed
     */
    public function hasToctreeChanged(array $oldToctree, array $newToctree): bool
    {
        // Simple comparison - if keys or values differ, structure changed
        if (count($oldToctree) !== count($newToctree)) {
            return true;
        }

        foreach ($oldToctree as $parent => $children) {
            if (!isset($newToctree[$parent])) {
                return true;
            }

            $oldChildren = $children;
            $newChildren = $newToctree[$parent];

            // Sort before comparing - order changes within a toctree entry
            // don't affect the dependency graph structure, only the rendered
            // navigation order. Navigation rendering is handled separately.
            sort($oldChildren);
            sort($newChildren);

            if ($oldChildren !== $newChildren) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the configured global patterns.
     *
     * @return string[]
     */
    public function getGlobalPatterns(): array
    {
        return $this->globalPatterns;
    }
}
