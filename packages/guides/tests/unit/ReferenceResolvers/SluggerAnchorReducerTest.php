<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SluggerAnchorReducerTest extends TestCase
{
    private SluggerAnchorNormalizer $subject;

    protected function setUp(): void
    {
        $this->subject = new SluggerAnchorNormalizer();
    }

    #[DataProvider('rawAnchorProvider')]
    public function testAnchorReduction(string $expected, string $rawInput): void
    {
        self::assertEquals($expected, $this->subject->reduceAnchor($rawInput));
    }

    /** @return Generator<string, array{string, string}> */
    public static function rawAnchorProvider(): Generator
    {
        yield 'lowercase, space to dash' => [
            'lorem-ipsum',
            'Lorem Ipsum',
        ];

        yield 'unchanged if already snaky' => [
            'lorem-ipsum',
            'lorem-ipsum',
        ];

        yield 'special signs to dash, max one dash' => [
            'lorem-ipsum',
            'lorem?!_-ipsum',
        ];

        yield 'special signs at start and end omitted' => [
            'lorem-ipsum',
            '!lorem?!_-ipsum?',
        ];
    }
}
