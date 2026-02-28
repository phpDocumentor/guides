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

final class PropagationResultTest extends TestCase
{
    public function testNeedsRenderingReturnsTrue(): void
    {
        $result = new PropagationResult(
            documentsToRender: ['doc1', 'doc2'],
            documentsToSkip: ['doc3'],
        );

        self::assertTrue($result->needsRendering('doc1'));
        self::assertTrue($result->needsRendering('doc2'));
    }

    public function testNeedsRenderingReturnsFalse(): void
    {
        $result = new PropagationResult(
            documentsToRender: ['doc1'],
            documentsToSkip: ['doc2', 'doc3'],
        );

        self::assertFalse($result->needsRendering('doc2'));
        self::assertFalse($result->needsRendering('doc3'));
        self::assertFalse($result->needsRendering('nonexistent'));
    }

    public function testGetRenderCount(): void
    {
        $result = new PropagationResult(
            documentsToRender: ['doc1', 'doc2', 'doc3'],
            documentsToSkip: ['doc4'],
        );

        self::assertSame(3, $result->getRenderCount());
    }

    public function testGetSkipCount(): void
    {
        $result = new PropagationResult(
            documentsToRender: ['doc1'],
            documentsToSkip: ['doc2', 'doc3', 'doc4', 'doc5'],
        );

        self::assertSame(4, $result->getSkipCount());
    }

    public function testGetSavingsRatioWithMixedDocuments(): void
    {
        // 1 to render, 3 to skip = 75% savings
        $result = new PropagationResult(
            documentsToRender: ['doc1'],
            documentsToSkip: ['doc2', 'doc3', 'doc4'],
        );

        self::assertEqualsWithDelta(0.75, $result->getSavingsRatio(), 0.001);
    }

    public function testGetSavingsRatioAllRender(): void
    {
        $result = new PropagationResult(
            documentsToRender: ['doc1', 'doc2'],
            documentsToSkip: [],
        );

        self::assertSame(0.0, $result->getSavingsRatio());
    }

    public function testGetSavingsRatioAllSkip(): void
    {
        $result = new PropagationResult(
            documentsToRender: [],
            documentsToSkip: ['doc1', 'doc2'],
        );

        self::assertSame(1.0, $result->getSavingsRatio());
    }

    public function testGetSavingsRatioEmpty(): void
    {
        $result = new PropagationResult(
            documentsToRender: [],
            documentsToSkip: [],
        );

        self::assertSame(0.0, $result->getSavingsRatio());
    }

    public function testPropagatedFrom(): void
    {
        $result = new PropagationResult(
            documentsToRender: ['doc1', 'doc2'],
            documentsToSkip: [],
            propagatedFrom: ['source1', 'source2'],
        );

        self::assertSame(['source1', 'source2'], $result->propagatedFrom);
    }

    public function testToArray(): void
    {
        $result = new PropagationResult(
            documentsToRender: ['doc1', 'doc2'],
            documentsToSkip: ['doc3'],
            propagatedFrom: ['source1'],
        );

        $array = $result->toArray();

        self::assertSame(['doc1', 'doc2'], $array['documentsToRender']);
        self::assertSame(['doc3'], $array['documentsToSkip']);
        self::assertSame(['source1'], $array['propagatedFrom']);
    }

    public function testFromArray(): void
    {
        $data = [
            'documentsToRender' => ['doc1', 'doc2'],
            'documentsToSkip' => ['doc3'],
            'propagatedFrom' => ['source1'],
        ];

        $result = PropagationResult::fromArray($data);

        self::assertSame(['doc1', 'doc2'], $result->documentsToRender);
        self::assertSame(['doc3'], $result->documentsToSkip);
        self::assertSame(['source1'], $result->propagatedFrom);
    }

    public function testFromArrayWithDefaults(): void
    {
        $result = PropagationResult::fromArray([]);

        self::assertSame([], $result->documentsToRender);
        self::assertSame([], $result->documentsToSkip);
        self::assertSame([], $result->propagatedFrom);
    }

    public function testSerializationRoundTrip(): void
    {
        $original = new PropagationResult(
            documentsToRender: ['a', 'b', 'c'],
            documentsToSkip: ['d', 'e'],
            propagatedFrom: ['a'],
        );

        $restored = PropagationResult::fromArray($original->toArray());

        self::assertSame($original->documentsToRender, $restored->documentsToRender);
        self::assertSame($original->documentsToSkip, $restored->documentsToSkip);
        self::assertSame($original->propagatedFrom, $restored->propagatedFrom);
    }

    public function testFromArrayThrowsOnInvalidDocumentsToRenderType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected documentsToRender to be array');

        PropagationResult::fromArray(['documentsToRender' => 'not-array']);
    }

    public function testFromArrayThrowsOnInvalidDocumentsToSkipType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected documentsToSkip to be array');

        PropagationResult::fromArray(['documentsToSkip' => 123]);
    }

    public function testFromArrayThrowsOnInvalidPropagatedFromType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected propagatedFrom to be array');

        PropagationResult::fromArray(['propagatedFrom' => null]);
    }

    public function testFromArrayThrowsOnNonStringItem(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected documentsToRender item to be string');

        PropagationResult::fromArray(['documentsToRender' => [123, 'valid']]);
    }

    public function testFromArrayThrowsOnExcessiveDocumentsToRender(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed maximum');

        $docs = [];
        for ($i = 0; $i < 100_001; $i++) {
            $docs[] = 'doc' . $i;
        }

        PropagationResult::fromArray(['documentsToRender' => $docs]);
    }

    public function testFromArrayThrowsOnExcessiveDocumentsToSkip(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed maximum');

        $docs = [];
        for ($i = 0; $i < 100_001; $i++) {
            $docs[] = 'doc' . $i;
        }

        PropagationResult::fromArray(['documentsToSkip' => $docs]);
    }
}
