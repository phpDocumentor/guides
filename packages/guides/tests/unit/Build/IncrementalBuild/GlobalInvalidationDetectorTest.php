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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function str_repeat;

final class GlobalInvalidationDetectorTest extends TestCase
{
    public function testRequiresFullRebuildWithSettingsChange(): void
    {
        $detector = new GlobalInvalidationDetector();
        $changes = new ChangeDetectionResult(
            dirty: [],
            clean: ['doc1'],
            new: [],
            deleted: [],
        );

        $result = $detector->requiresFullRebuild($changes, 'new-hash', 'old-hash');

        self::assertTrue($result);
    }

    public function testRequiresFullRebuildWithSameSettings(): void
    {
        $detector = new GlobalInvalidationDetector();
        $changes = new ChangeDetectionResult(
            dirty: ['doc1'],
            clean: [],
            new: [],
            deleted: [],
        );

        $result = $detector->requiresFullRebuild($changes, 'same-hash', 'same-hash');

        self::assertFalse($result);
    }

    public function testRequiresFullRebuildWithGuidesXml(): void
    {
        $detector = new GlobalInvalidationDetector();
        $changes = new ChangeDetectionResult(
            dirty: ['path/to/guides.xml'],
            clean: [],
            new: [],
            deleted: [],
        );

        self::assertTrue($detector->requiresFullRebuild($changes));
    }

    public function testRequiresFullRebuildWithSettingsCfg(): void
    {
        $detector = new GlobalInvalidationDetector();
        $changes = new ChangeDetectionResult(
            dirty: [],
            clean: [],
            new: ['Settings.cfg'],
            deleted: [],
        );

        self::assertTrue($detector->requiresFullRebuild($changes));
    }

    public function testRequiresFullRebuildWithConfPy(): void
    {
        $detector = new GlobalInvalidationDetector();
        $changes = new ChangeDetectionResult(
            dirty: [],
            clean: [],
            new: [],
            deleted: ['conf.py'],
        );

        self::assertTrue($detector->requiresFullRebuild($changes));
    }

    public function testRequiresFullRebuildWithStaticDirectory(): void
    {
        $detector = new GlobalInvalidationDetector();
        $changes = new ChangeDetectionResult(
            dirty: ['_static/custom.css'],
            clean: [],
            new: [],
            deleted: [],
        );

        self::assertTrue($detector->requiresFullRebuild($changes));
    }

    public function testRequiresFullRebuildWithTemplatesDirectory(): void
    {
        $detector = new GlobalInvalidationDetector();
        $changes = new ChangeDetectionResult(
            dirty: [],
            clean: [],
            new: ['project/_templates/layout.html'],
            deleted: [],
        );

        self::assertTrue($detector->requiresFullRebuild($changes));
    }

    public function testRequiresFullRebuildWithObjectsInv(): void
    {
        $detector = new GlobalInvalidationDetector();
        $changes = new ChangeDetectionResult(
            dirty: ['path/to/objects.inv'],
            clean: [],
            new: [],
            deleted: [],
        );

        self::assertTrue($detector->requiresFullRebuild($changes));
    }

    public function testRequiresFullRebuildWithRegularDocChange(): void
    {
        $detector = new GlobalInvalidationDetector();
        $changes = new ChangeDetectionResult(
            dirty: ['docs/index.rst', 'docs/chapter1.rst'],
            clean: ['docs/chapter2.rst'],
            new: [],
            deleted: [],
        );

        self::assertFalse($detector->requiresFullRebuild($changes));
    }

    public function testCustomGlobalPatterns(): void
    {
        $detector = new GlobalInvalidationDetector([
            'custom-config.yaml',
            'my-templates/',
        ]);

        $changes = new ChangeDetectionResult(
            dirty: ['custom-config.yaml'],
            clean: [],
            new: [],
            deleted: [],
        );

        self::assertTrue($detector->requiresFullRebuild($changes));

        // Default pattern should not trigger
        $changes2 = new ChangeDetectionResult(
            dirty: ['guides.xml'],
            clean: [],
            new: [],
            deleted: [],
        );

        self::assertFalse($detector->requiresFullRebuild($changes2));
    }

    public function testCustomDirectoryPattern(): void
    {
        $detector = new GlobalInvalidationDetector(['assets/']);

        $changes = new ChangeDetectionResult(
            dirty: ['project/assets/logo.png'],
            clean: [],
            new: [],
            deleted: [],
        );

        self::assertTrue($detector->requiresFullRebuild($changes));
    }

    public function testGetGlobalPatterns(): void
    {
        $patterns = ['custom.xml', 'theme/'];
        $detector = new GlobalInvalidationDetector($patterns);

        self::assertSame($patterns, $detector->getGlobalPatterns());
    }

    public function testGetDefaultGlobalPatterns(): void
    {
        $detector = new GlobalInvalidationDetector();
        $patterns = $detector->getGlobalPatterns();

        self::assertContains('guides.xml', $patterns);
        self::assertContains('Settings.cfg', $patterns);
        self::assertContains('conf.py', $patterns);
        self::assertContains('_static/', $patterns);
        self::assertContains('_templates/', $patterns);
        self::assertContains('objects.inv', $patterns);
    }

    public function testIsGlobalFileWithExactMatch(): void
    {
        $detector = new GlobalInvalidationDetector();

        self::assertTrue($detector->isGlobalFile('guides.xml'));
        self::assertTrue($detector->isGlobalFile('/path/to/guides.xml'));
    }

    public function testIsGlobalFileWithDirectoryPattern(): void
    {
        $detector = new GlobalInvalidationDetector();

        self::assertTrue($detector->isGlobalFile('_static/file.css'));
        self::assertTrue($detector->isGlobalFile('project/_static/script.js'));
    }

    public function testDirectoryPatternDoesNotMatchPartialNames(): void
    {
        $detector = new GlobalInvalidationDetector(['foo/']);

        // Should match complete directory name
        self::assertTrue($detector->isGlobalFile('foo/bar.txt'));
        self::assertTrue($detector->isGlobalFile('path/foo/bar.txt'));
        self::assertTrue($detector->isGlobalFile('/absolute/foo/bar.txt'));

        // Should NOT match partial directory names
        self::assertFalse($detector->isGlobalFile('prefix_foo/bar.txt'));
        self::assertFalse($detector->isGlobalFile('path/prefix_foo/bar.txt'));
        self::assertFalse($detector->isGlobalFile('foobar/file.txt'));
    }

    public function testIsGlobalFileNormalizesBackslashes(): void
    {
        $detector = new GlobalInvalidationDetector();

        // Windows-style paths
        self::assertTrue($detector->isGlobalFile('project\\_static\\file.css'));
        self::assertTrue($detector->isGlobalFile('docs\\guides.xml'));
    }

    public function testHasToctreeChangedWithNoChange(): void
    {
        $detector = new GlobalInvalidationDetector();
        $toctree = [
            'index' => ['chapter1', 'chapter2'],
            'chapter1' => ['section1', 'section2'],
        ];

        self::assertFalse($detector->hasToctreeChanged($toctree, $toctree));
    }

    public function testHasToctreeChangedWithDifferentCount(): void
    {
        $detector = new GlobalInvalidationDetector();
        $old = ['index' => ['chapter1']];
        $new = [
            'index' => ['chapter1'],
            'chapter1' => ['section1'],
        ];

        self::assertTrue($detector->hasToctreeChanged($old, $new));
    }

    public function testHasToctreeChangedWithMissingParent(): void
    {
        $detector = new GlobalInvalidationDetector();
        $old = [
            'index' => ['chapter1'],
            'chapter1' => ['section1'],
        ];
        $new = [
            'index' => ['chapter1'],
            'chapter2' => ['section1'], // Different parent
        ];

        self::assertTrue($detector->hasToctreeChanged($old, $new));
    }

    public function testHasToctreeChangedWithDifferentChildren(): void
    {
        $detector = new GlobalInvalidationDetector();
        $old = ['index' => ['chapter1', 'chapter2']];
        $new = ['index' => ['chapter1', 'chapter3']]; // chapter3 instead of chapter2

        self::assertTrue($detector->hasToctreeChanged($old, $new));
    }

    public function testHasToctreeChangedIgnoresOrder(): void
    {
        $detector = new GlobalInvalidationDetector();
        $old = ['index' => ['chapter1', 'chapter2']];
        $new = ['index' => ['chapter2', 'chapter1']]; // Different order but same children

        // Currently sorts before comparing, so order doesn't matter
        self::assertFalse($detector->hasToctreeChanged($old, $new));
    }

    public function testHasToctreeChangedWithEmptyToctrees(): void
    {
        $detector = new GlobalInvalidationDetector();

        self::assertFalse($detector->hasToctreeChanged([], []));
    }

    public function testRequiresFullRebuildWithNullHashes(): void
    {
        $detector = new GlobalInvalidationDetector();
        $changes = new ChangeDetectionResult(
            dirty: [],
            clean: ['doc1'],
            new: [],
            deleted: [],
        );

        // Both null - no comparison needed
        self::assertFalse($detector->requiresFullRebuild($changes, null, null));

        // One null - no comparison
        self::assertFalse($detector->requiresFullRebuild($changes, 'hash', null));
        self::assertFalse($detector->requiresFullRebuild($changes, null, 'hash'));
    }

    public function testConstructorThrowsOnEmptyPattern(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Global pattern cannot be empty');

        new GlobalInvalidationDetector(['valid.xml', '']);
    }

    public function testConstructorThrowsOnTooLongPattern(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Global pattern exceeds maximum length');

        // Pattern longer than 256 characters
        $longPattern = str_repeat('a', 257);
        new GlobalInvalidationDetector([$longPattern]);
    }

    /** @param array<mixed> $patterns */
    #[DataProvider('invalidPatternTypesProvider')]
    public function testConstructorThrowsOnNonStringPattern(array $patterns): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Global pattern must be a string');

        /** @phpstan-ignore argument.type (testing invalid input validation) */
        new GlobalInvalidationDetector($patterns);
    }

    /** @return array<string, array<mixed>> */
    public static function invalidPatternTypesProvider(): array
    {
        return [
            'integer' => [[123]],
            'array' => [[['nested']]],
            'null' => [[null]],
            'boolean' => [[true]],
            'float' => [[3.14]],
        ];
    }
}
