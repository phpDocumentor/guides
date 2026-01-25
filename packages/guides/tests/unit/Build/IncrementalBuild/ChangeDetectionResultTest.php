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

final class ChangeDetectionResultTest extends TestCase
{
    public function testGetFilesToProcessCombinesDirtyAndNew(): void
    {
        $result = new ChangeDetectionResult(
            dirty: ['file1.rst', 'file2.rst'],
            clean: ['file3.rst'],
            new: ['file4.rst', 'file5.rst'],
            deleted: ['old.rst'],
        );

        self::assertSame(
            ['file1.rst', 'file2.rst', 'file4.rst', 'file5.rst'],
            $result->getFilesToProcess(),
        );
    }

    public function testHasChangesReturnsTrueForDirtyFiles(): void
    {
        $result = new ChangeDetectionResult(
            dirty: ['changed.rst'],
            clean: [],
            new: [],
            deleted: [],
        );

        self::assertTrue($result->hasChanges());
    }

    public function testHasChangesReturnsTrueForNewFiles(): void
    {
        $result = new ChangeDetectionResult(
            dirty: [],
            clean: [],
            new: ['new.rst'],
            deleted: [],
        );

        self::assertTrue($result->hasChanges());
    }

    public function testHasChangesReturnsTrueForDeletedFiles(): void
    {
        $result = new ChangeDetectionResult(
            dirty: [],
            clean: [],
            new: [],
            deleted: ['deleted.rst'],
        );

        self::assertTrue($result->hasChanges());
    }

    public function testHasChangesReturnsFalseWhenOnlyClean(): void
    {
        $result = new ChangeDetectionResult(
            dirty: [],
            clean: ['file1.rst', 'file2.rst'],
            new: [],
            deleted: [],
        );

        self::assertFalse($result->hasChanges());
    }

    public function testGetChangeCountSumsCorrectly(): void
    {
        $result = new ChangeDetectionResult(
            dirty: ['d1.rst', 'd2.rst'],
            clean: ['c1.rst', 'c2.rst', 'c3.rst'],
            new: ['n1.rst'],
            deleted: ['del1.rst', 'del2.rst', 'del3.rst'],
        );

        // dirty(2) + new(1) + deleted(3) = 6
        self::assertSame(6, $result->getChangeCount());
    }

    public function testGetChangeCountDoesNotCountClean(): void
    {
        $result = new ChangeDetectionResult(
            dirty: [],
            clean: ['c1.rst', 'c2.rst', 'c3.rst'],
            new: [],
            deleted: [],
        );

        self::assertSame(0, $result->getChangeCount());
    }

    public function testToArrayReturnsAllCategories(): void
    {
        $result = new ChangeDetectionResult(
            dirty: ['dirty.rst'],
            clean: ['clean.rst'],
            new: ['new.rst'],
            deleted: ['deleted.rst'],
        );

        self::assertSame([
            'dirty' => ['dirty.rst'],
            'clean' => ['clean.rst'],
            'new' => ['new.rst'],
            'deleted' => ['deleted.rst'],
        ], $result->toArray());
    }

    public function testEmptyResult(): void
    {
        $result = new ChangeDetectionResult(
            dirty: [],
            clean: [],
            new: [],
            deleted: [],
        );

        self::assertSame([], $result->getFilesToProcess());
        self::assertFalse($result->hasChanges());
        self::assertSame(0, $result->getChangeCount());
    }
}
