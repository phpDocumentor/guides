<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use Faker\Generator;
use Faker\Factory;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;
use phpDocumentor\Guides\Span\CrossReferenceNode;
use phpDocumentor\Guides\Span\LiteralToken;
use phpDocumentor\Guides\Span\SpanToken;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use function current;

final class LineCheckerTest extends TestCase
{
    /** @dataProvider specialLineProvider */
    public function testSpecialLine(string $input, int $max, ?string $expected): void
    {
        self::assertEquals($expected, LineChecker::isSpecialLine($input, $max));
    }

    /** @return array<int, array<int, int|string|null>> */
    public function specialLineProvider(): array
    {
        return [
            ['', 2, null],
            ['=', 2, null],
            ['==', 2, '='],
            ['   ', 2, null],
            ['=-=', 2, null],
            ['==-', 2, null],
            ['-==', 2, null],
            ['===', 2, '='],
            ['===', 4, null],
            ['====', 4, '='],
            ['================================================', 2, '='],
        ];
    }
}
