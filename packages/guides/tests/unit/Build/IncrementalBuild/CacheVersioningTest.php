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

use PHPUnit\Framework\TestCase;

use function time;

use const PHP_MAJOR_VERSION;
use const PHP_MINOR_VERSION;
use const PHP_VERSION;

final class CacheVersioningTest extends TestCase
{
    public function testIsCacheValidWithValidMetadata(): void
    {
        $versioning = new CacheVersioning();
        $metadata = $versioning->createMetadata();

        self::assertTrue($versioning->isCacheValid($metadata));
    }

    public function testIsCacheValidWithOldVersion(): void
    {
        $versioning = new CacheVersioning();
        $metadata = [
            'version' => 0, // Old/different version
            'phpVersion' => PHP_VERSION,
        ];

        self::assertFalse($versioning->isCacheValid($metadata));
    }

    public function testIsCacheValidWithDifferentPhpMajorVersion(): void
    {
        $versioning = new CacheVersioning();
        $metadata = $versioning->createMetadata();
        $metadata['phpVersion'] = '7.4.0'; // Different major.minor version

        // Only false if current PHP is not 7.4
        if (PHP_MAJOR_VERSION !== 7 || PHP_MINOR_VERSION !== 4) {
            self::assertFalse($versioning->isCacheValid($metadata));
        } else {
            self::assertTrue($versioning->isCacheValid($metadata));
        }
    }

    public function testIsCacheValidWithSameMajorMinorVersion(): void
    {
        $versioning = new CacheVersioning();
        $metadata = $versioning->createMetadata();
        // Simulate different patch version (should still be valid)
        $metadata['phpVersion'] = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.999';

        self::assertTrue($versioning->isCacheValid($metadata));
    }

    public function testIsCacheValidWithEmptyMetadata(): void
    {
        $versioning = new CacheVersioning();

        self::assertFalse($versioning->isCacheValid([]));
    }

    public function testIsCacheValidWithMissingPhpVersion(): void
    {
        $versioning = new CacheVersioning();
        $metadata = ['version' => 1]; // Missing phpVersion

        self::assertFalse($versioning->isCacheValid($metadata));
    }

    public function testCreateMetadata(): void
    {
        $versioning = new CacheVersioning('2.0.0');
        $metadata = $versioning->createMetadata('settings-hash-123');

        self::assertSame(1, $metadata['version']);
        self::assertSame(PHP_VERSION, $metadata['phpVersion']);
        self::assertSame('2.0.0', $metadata['packageVersion']);
        self::assertSame('settings-hash-123', $metadata['settingsHash']);
        self::assertIsInt($metadata['createdAt']);
        self::assertGreaterThan(0, $metadata['createdAt']);
    }

    public function testCreateMetadataWithEmptySettingsHash(): void
    {
        $versioning = new CacheVersioning();
        $metadata = $versioning->createMetadata();

        self::assertSame('', $metadata['settingsHash']);
    }

    public function testGetCacheVersion(): void
    {
        $versioning = new CacheVersioning();

        self::assertSame(1, $versioning->getCacheVersion());
    }

    public function testDefaultPackageVersion(): void
    {
        $versioning = new CacheVersioning();
        $metadata = $versioning->createMetadata();

        self::assertSame('1.0.0', $metadata['packageVersion']);
    }

    public function testCustomPackageVersion(): void
    {
        $versioning = new CacheVersioning('3.14.159');
        $metadata = $versioning->createMetadata();

        self::assertSame('3.14.159', $metadata['packageVersion']);
    }

    public function testCreatedAtTimestamp(): void
    {
        $before = time();
        $versioning = new CacheVersioning();
        $metadata = $versioning->createMetadata();
        $after = time();

        self::assertGreaterThanOrEqual($before, $metadata['createdAt']);
        self::assertLessThanOrEqual($after, $metadata['createdAt']);
    }

    public function testIsCacheValidWithSameMajorPackageVersion(): void
    {
        $versioning = new CacheVersioning('1.5.0');
        $metadata = $versioning->createMetadata();
        // Simulate cache from different minor version
        $metadata['packageVersion'] = '1.2.3';

        self::assertTrue($versioning->isCacheValid($metadata));
    }

    public function testIsCacheValidWithDifferentMajorPackageVersion(): void
    {
        $versioning = new CacheVersioning('2.0.0');
        $metadata = $versioning->createMetadata();
        // Simulate cache from v1
        $metadata['packageVersion'] = '1.9.9';

        self::assertFalse($versioning->isCacheValid($metadata));
    }

    public function testIsCacheValidWithVPrefixedVersion(): void
    {
        $versioning = new CacheVersioning('v1.5.0');
        $metadata = $versioning->createMetadata();
        // Both with v prefix
        $metadata['packageVersion'] = 'v1.2.3';

        self::assertTrue($versioning->isCacheValid($metadata));
    }

    public function testIsCacheValidWithMixedVPrefix(): void
    {
        $versioning = new CacheVersioning('1.5.0');
        $metadata = $versioning->createMetadata();
        // Cache has v prefix, current doesn't
        $metadata['packageVersion'] = 'v1.2.3';

        self::assertTrue($versioning->isCacheValid($metadata));
    }

    public function testIsCacheValidWithInvalidPackageVersion(): void
    {
        $versioning = new CacheVersioning('1.0.0');
        $metadata = $versioning->createMetadata();
        // Invalid version string
        $metadata['packageVersion'] = 'not-a-version';

        self::assertFalse($versioning->isCacheValid($metadata));
    }

    public function testIsCacheValidWithMissingPackageVersion(): void
    {
        $versioning = new CacheVersioning('1.0.0');
        $metadata = $versioning->createMetadata();
        unset($metadata['packageVersion']);

        self::assertFalse($versioning->isCacheValid($metadata));
    }

    public function testIsCacheValidWithNonStringPackageVersion(): void
    {
        $versioning = new CacheVersioning('1.0.0');
        $metadata = $versioning->createMetadata();
        $metadata['packageVersion'] = 123; // Non-string

        self::assertFalse($versioning->isCacheValid($metadata));
    }

    public function testIsCacheValidWithMajorVersionZero(): void
    {
        $versioning = new CacheVersioning('0.9.0');
        $metadata = $versioning->createMetadata();
        $metadata['packageVersion'] = '0.1.0';

        self::assertTrue($versioning->isCacheValid($metadata));
    }

    public function testIsCacheValidWithPreReleaseVersion(): void
    {
        $versioning = new CacheVersioning('1.0.0-alpha');
        $metadata = $versioning->createMetadata();
        $metadata['packageVersion'] = '1.2.3-beta.1';

        // Major version is still 1, so compatible
        self::assertTrue($versioning->isCacheValid($metadata));
    }

    public function testIsCacheValidWithMajorOnlyVersion(): void
    {
        $versioning = new CacheVersioning('2');
        $metadata = $versioning->createMetadata();
        $metadata['packageVersion'] = '2.0.0';

        self::assertTrue($versioning->isCacheValid($metadata));
    }
}
