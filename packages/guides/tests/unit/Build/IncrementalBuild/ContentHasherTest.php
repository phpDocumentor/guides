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

use function file_exists;
use function file_put_contents;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

final class ContentHasherTest extends TestCase
{
    private ContentHasher $hasher;
    private string $tempFile;

    protected function setUp(): void
    {
        $this->hasher = new ContentHasher();
        $this->tempFile = sys_get_temp_dir() . '/content-hasher-test-' . uniqid() . '.txt';
    }

    protected function tearDown(): void
    {
        if (!file_exists($this->tempFile)) {
            return;
        }

        unlink($this->tempFile);
    }

    public function testHashFileReturnsConsistentHash(): void
    {
        file_put_contents($this->tempFile, 'test content');

        $hash1 = $this->hasher->hashFile($this->tempFile);
        $hash2 = $this->hasher->hashFile($this->tempFile);

        self::assertSame($hash1, $hash2);
        self::assertNotEmpty($hash1);
    }

    public function testHashFileReturnsDifferentHashForDifferentContent(): void
    {
        file_put_contents($this->tempFile, 'content A');
        $hash1 = $this->hasher->hashFile($this->tempFile);

        file_put_contents($this->tempFile, 'content B');
        $hash2 = $this->hasher->hashFile($this->tempFile);

        self::assertNotSame($hash1, $hash2);
    }

    public function testHashFileReturnsEmptyForNonexistentFile(): void
    {
        $hash = $this->hasher->hashFile('/nonexistent/file.txt');

        self::assertSame('', $hash);
    }

    public function testHashContentReturnsConsistentHash(): void
    {
        $hash1 = $this->hasher->hashContent('test string');
        $hash2 = $this->hasher->hashContent('test string');

        self::assertSame($hash1, $hash2);
        self::assertNotEmpty($hash1);
    }

    public function testHashExportsIsDeterministic(): void
    {
        $anchors = ['section-a' => 'Section A', 'section-b' => 'Section B'];
        $titles = ['h1' => 'Title One', 'h2' => 'Title Two'];
        $citations = ['cite1', 'cite2'];

        $hash1 = $this->hasher->hashExports($anchors, $titles, $citations, 'Doc Title');
        $hash2 = $this->hasher->hashExports($anchors, $titles, $citations, 'Doc Title');

        self::assertSame($hash1, $hash2);
    }

    public function testHashExportsChangesWithDifferentAnchors(): void
    {
        $titles = ['h1' => 'Title'];
        $citations = [];

        $hash1 = $this->hasher->hashExports(['a' => 'A'], $titles, $citations, '');
        $hash2 = $this->hasher->hashExports(['b' => 'B'], $titles, $citations, '');

        self::assertNotSame($hash1, $hash2);
    }

    public function testGetAlgorithmReturnsValidAlgorithm(): void
    {
        $algorithm = $this->hasher->getAlgorithm();

        self::assertContains($algorithm, ['xxh128', 'sha256']);
    }
}
