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

use function explode;
use function is_numeric;
use function is_string;
use function str_starts_with;
use function substr;
use function time;

use const PHP_MAJOR_VERSION;
use const PHP_MINOR_VERSION;
use const PHP_VERSION;

/**
 * Handles cache versioning and validation.
 *
 * Cache is invalidated when:
 * - Cache format version changes
 * - PHP major.minor version changes (affects serialization)
 * - Package major version changes (may have breaking changes)
 */
final class CacheVersioning
{
    /**
     * Current cache format version.
     * Increment when cache structure changes incompatibly.
     */
    private const CACHE_VERSION = 1;

    /** Minimum valid major version for package version string */
    private const MIN_VERSION_MAJOR = 0;

    /** @param string $packageVersion Package version for additional validation */
    public function __construct(
        private readonly string $packageVersion = '1.0.0',
    ) {
    }

    /**
     * Check if cached metadata is still valid.
     *
     * @param array<string, mixed> $metadata Cached metadata
     *
     * @return bool True if cache is valid
     */
    public function isCacheValid(array $metadata): bool
    {
        // Check cache version
        if (($metadata['version'] ?? 0) !== self::CACHE_VERSION) {
            return false;
        }

        // Check PHP major.minor version (patch changes are compatible)
        $cachedPhpVersion = $metadata['phpVersion'] ?? '';
        if (!is_string($cachedPhpVersion)) {
            return false;
        }

        $currentPhpMajor = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
        if (!str_starts_with($cachedPhpVersion, $currentPhpMajor)) {
            return false;
        }

        // Check package major version (major version changes may break cache compatibility)
        $cachedPackageVersion = $metadata['packageVersion'] ?? '';
        if (!is_string($cachedPackageVersion)) {
            return false;
        }

        return $this->isMajorVersionCompatible($cachedPackageVersion, $this->packageVersion);
    }

    /**
     * Check if two version strings have the same major version.
     */
    private function isMajorVersionCompatible(string $cached, string $current): bool
    {
        $cachedMajor = $this->extractMajorVersion($cached);
        $currentMajor = $this->extractMajorVersion($current);

        // If either can't be parsed, assume incompatible
        if ($cachedMajor === null || $currentMajor === null) {
            return false;
        }

        return $cachedMajor === $currentMajor;
    }

    /**
     * Extract major version from semver string.
     *
     * @return int|null Major version or null if unparseable
     */
    private function extractMajorVersion(string $version): int|null
    {
        // Handle versions with 'v' prefix (e.g., 'v1.2.3')
        if (str_starts_with($version, 'v')) {
            $version = substr($version, 1);
        }

        $parts = explode('.', $version);
        if ($parts === [] || !is_numeric($parts[0])) {
            return null;
        }

        $major = (int) $parts[0];

        return $major >= self::MIN_VERSION_MAJOR ? $major : null;
    }

    /**
     * Create metadata for cache persistence.
     *
     * @param string $settingsHash Hash of project settings
     *
     * @return array<string, mixed>
     */
    public function createMetadata(string $settingsHash = ''): array
    {
        return [
            'version' => self::CACHE_VERSION,
            'phpVersion' => PHP_VERSION,
            'packageVersion' => $this->packageVersion,
            'settingsHash' => $settingsHash,
            'createdAt' => time(),
        ];
    }

    /**
     * Get current cache version.
     */
    public function getCacheVersion(): int
    {
        return self::CACHE_VERSION;
    }
}
