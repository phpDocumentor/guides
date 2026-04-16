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
use PHPUnit\Framework\TestCase;

use function str_repeat;

final class DocumentExportsTest extends TestCase
{
    public function testHasExportsChangedReturnsFalseForSameHash(): void
    {
        $exports1 = $this->createExports('hash1', 'exports-hash');
        $exports2 = $this->createExports('hash2', 'exports-hash');

        self::assertFalse($exports1->hasExportsChanged($exports2));
    }

    public function testHasExportsChangedReturnsTrueForDifferentHash(): void
    {
        $exports1 = $this->createExports('hash1', 'exports-hash-1');
        $exports2 = $this->createExports('hash1', 'exports-hash-2');

        self::assertTrue($exports1->hasExportsChanged($exports2));
    }

    public function testHasContentChangedReturnsFalseForSameHash(): void
    {
        $exports1 = $this->createExports('content-hash', 'exports1');
        $exports2 = $this->createExports('content-hash', 'exports2');

        self::assertFalse($exports1->hasContentChanged($exports2));
    }

    public function testHasContentChangedReturnsTrueForDifferentHash(): void
    {
        $exports1 = $this->createExports('content-hash-1', 'exports');
        $exports2 = $this->createExports('content-hash-2', 'exports');

        self::assertTrue($exports1->hasContentChanged($exports2));
    }

    public function testHasExportsChangedWithEmptyHashes(): void
    {
        $exports1 = $this->createExports('hash', '');
        $exports2 = $this->createExports('hash', '');

        self::assertFalse($exports1->hasExportsChanged($exports2));
    }

    public function testGetAnchorNames(): void
    {
        $exports = new DocumentExports(
            documentPath: 'test.rst',
            contentHash: 'hash',
            exportsHash: 'exports',
            anchors: ['anchor1' => 'Title 1', 'anchor2' => 'Title 2'],
            sectionTitles: [],
            citations: [],
            lastModified: 0,
        );

        self::assertSame(['anchor1', 'anchor2'], $exports->getAnchorNames());
    }

    public function testSerializationRoundTrip(): void
    {
        $original = new DocumentExports(
            documentPath: 'path/to/doc.rst',
            contentHash: 'abc123abc123abc123abc123abc12345', // 32 chars (xxh128)
            exportsHash: 'def456def456def456def456def456def456def456def456def456def456def4', // 64 chars (sha256)
            anchors: ['anchor1' => 'Title'],
            sectionTitles: ['section1' => 'Section Title'],
            citations: ['citation1'],
            lastModified: 1_234_567_890,
            documentTitle: 'Document Title',
        );

        $array = $original->toArray();
        $restored = DocumentExports::fromArray($array);

        self::assertSame($original->documentPath, $restored->documentPath);
        self::assertSame($original->contentHash, $restored->contentHash);
        self::assertSame($original->exportsHash, $restored->exportsHash);
        self::assertSame($original->anchors, $restored->anchors);
        self::assertSame($original->sectionTitles, $restored->sectionTitles);
        self::assertSame($original->citations, $restored->citations);
        self::assertSame($original->lastModified, $restored->lastModified);
        self::assertSame($original->documentTitle, $restored->documentTitle);
    }

    public function testFromArrayWithInvalidAnchorsType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected anchors to be array');

        DocumentExports::fromArray(['anchors' => 'not-an-array']);
    }

    public function testFromArrayWithInvalidAnchorsValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected anchors value to be string');

        DocumentExports::fromArray(['anchors' => ['key' => 123]]);
    }

    public function testFromArrayWithInvalidSectionTitlesType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected sectionTitles to be array');

        DocumentExports::fromArray(['sectionTitles' => 'not-an-array']);
    }

    public function testFromArrayWithInvalidSectionTitlesValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected sectionTitles value to be string');

        DocumentExports::fromArray(['sectionTitles' => ['key' => null]]);
    }

    public function testFromArrayWithInvalidCitationsType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected citations to be array');

        DocumentExports::fromArray(['citations' => 'not-an-array']);
    }

    public function testFromArrayWithInvalidCitationsItem(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected citations item to be string');

        DocumentExports::fromArray(['citations' => [123]]);
    }

    public function testFromArrayWithInvalidDocumentPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected documentPath to be string');

        DocumentExports::fromArray(['documentPath' => 123]);
    }

    public function testFromArrayWithInvalidLastModified(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected lastModified to be int between');

        DocumentExports::fromArray(['lastModified' => 'not-an-int']);
    }

    public function testFromArrayWithDefaults(): void
    {
        $exports = DocumentExports::fromArray([]);

        self::assertSame('', $exports->documentPath);
        self::assertSame('', $exports->contentHash);
        self::assertSame('', $exports->exportsHash);
        self::assertSame([], $exports->anchors);
        self::assertSame([], $exports->sectionTitles);
        self::assertSame([], $exports->citations);
        self::assertSame(0, $exports->lastModified);
        self::assertSame('', $exports->documentTitle);
    }

    public function testFromArrayWithInvalidContentHashFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('contentHash must be a hexadecimal string');

        DocumentExports::fromArray(['contentHash' => 'not-hex-format!']);
    }

    public function testFromArrayWithInvalidExportsHashFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exportsHash must be a hexadecimal string');

        DocumentExports::fromArray(['exportsHash' => 'contains spaces and symbols!']);
    }

    public function testFromArrayAcceptsValidHexHashes(): void
    {
        // 32-char hash (xxh128 format)
        $xxh128Hash = 'abc123DEF456abc123DEF456abc12345';
        // 64-char hash (sha256 format)
        $sha256Hash = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

        $exports = DocumentExports::fromArray([
            'contentHash' => $xxh128Hash,
            'exportsHash' => $sha256Hash,
        ]);

        self::assertSame($xxh128Hash, $exports->contentHash);
        self::assertSame($sha256Hash, $exports->exportsHash);
    }

    public function testFromArrayThrowsOnInvalidHashLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be 32 (xxh128) or 64 (sha256) hex chars');

        // 16 chars is invalid (neither 32 nor 64)
        DocumentExports::fromArray(['contentHash' => '0123456789abcdef']);
    }

    public function testFromArrayAcceptsEmptyHashes(): void
    {
        $exports = DocumentExports::fromArray([
            'contentHash' => '',
            'exportsHash' => '',
        ]);

        self::assertSame('', $exports->contentHash);
        self::assertSame('', $exports->exportsHash);
    }

    public function testFromArrayThrowsOnNegativeLastModified(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('lastModified to be int between');

        DocumentExports::fromArray(['lastModified' => -1]);
    }

    public function testFromArrayThrowsOnExcessiveLastModified(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('lastModified to be int between');

        // Year 3001 - exceeds MAX_TIMESTAMP
        DocumentExports::fromArray(['lastModified' => 32_535_216_000]);
    }

    public function testFromArrayThrowsOnExcessiveAnchorsCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds maximum');

        // Create array with more than MAX_ARRAY_ITEMS (10000) anchors
        $anchors = [];
        for ($i = 0; $i < 10_001; $i++) {
            $anchors['anchor' . $i] = 'Title ' . $i;
        }

        DocumentExports::fromArray(['anchors' => $anchors]);
    }

    public function testFromArrayThrowsOnExcessiveCitationsCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds maximum');

        // Create array with more than MAX_ARRAY_ITEMS (10000) citations
        $citations = [];
        for ($i = 0; $i < 10_001; $i++) {
            $citations[] = 'citation' . $i;
        }

        DocumentExports::fromArray(['citations' => $citations]);
    }

    public function testFromArrayThrowsOnOverlongDocumentPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds maximum length');

        // Create string longer than MAX_STRING_LENGTH (65536)
        $longPath = str_repeat('a', 65_537);

        DocumentExports::fromArray(['documentPath' => $longPath]);
    }

    public function testFromArrayThrowsOnControlCharactersInDocumentPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('contains invalid control characters');

        // Null byte in path - could cause filesystem issues
        DocumentExports::fromArray(['documentPath' => "docs/test\x00.rst"]);
    }

    public function testFromArrayThrowsOnNewlineInDocumentPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('contains invalid control characters');

        // Newline could affect log parsing
        DocumentExports::fromArray(['documentPath' => "docs/test\nmalicious"]);
    }

    public function testFromArrayAcceptsValidDocumentPath(): void
    {
        // Valid paths with allowed special characters
        $exports = DocumentExports::fromArray(['documentPath' => 'docs/sub-dir/my_file.rst']);

        self::assertSame('docs/sub-dir/my_file.rst', $exports->documentPath);
    }

    private function createExports(string $contentHash, string $exportsHash): DocumentExports
    {
        return new DocumentExports(
            documentPath: 'test.rst',
            contentHash: $contentHash,
            exportsHash: $exportsHash,
            anchors: [],
            sectionTitles: [],
            citations: [],
            lastModified: 0,
        );
    }
}
