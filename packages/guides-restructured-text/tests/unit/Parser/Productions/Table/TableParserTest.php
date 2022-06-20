<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\Table;

use phpDocumentor\Guides\RestructuredText\Parser\Productions\TableRule;
use PHPUnit\Framework\TestCase;

final class TableParserTest extends TestCase
{
    /**
     * @dataProvider tableLineHeaderProvider
     */
    public function testGuessTableType(string $input, string $expectedType): void
    {
        $tableParser = new TableParser();
        self::assertSame($expectedType, $tableParser->guessTableType($input));
    }

    public function tableLineHeaderProvider(): array
    {
        return [
            'simple table header line' => [
                '====== ====',
                TableRule::TYPE_SIMPLE
            ],
            'pretty table header line' => [
                '+======+====+',
                TableRule::TYPE_PRETTY
            ],
        ];
    }

    /**
     * @dataProvider tableLineProvider
     * @dataProvider malformedTableLineProvider
     */
    public function testParseTableLineSeparator(string $input, ?TableSeparatorLineConfig $expected)
    {
        $tableParser = new TableParser();

        self::assertEquals($expected, $tableParser->parseTableSeparatorLine($input));
    }

    public function tableLineProvider()
    {
        yield 'simple table header line' => [
            '== ===',
            new TableSeparatorLineConfig(
                true,
                TableRule::TYPE_SIMPLE,
                [
                    0 => [0, 2],
                    1 => [3, 6]
                ],
                '=',
                '== ===',
            )
        ];

        yield 'simple table normal line' => [
            '-- ---',
            new TableSeparatorLineConfig(
                false,
                TableRule::TYPE_SIMPLE,
                [
                    0 => [0, 2],
                    1 => [3, 6]
                ],
                '-',
                '-- ---',
            )
        ];

        yield 'grid table header line' => [
            '+==+===+',
            new TableSeparatorLineConfig(
                true,
                TableRule::TYPE_PRETTY,
                [
                    0 => [1, 3],
                    1 => [4, 7],
                ],
                '=',
                '+==+===+',
            )
        ];

        yield 'grid table normal line' => [
            '+--+---+',
            new TableSeparatorLineConfig(
                false,
                TableRule::TYPE_PRETTY,
                [
                    0 => [1, 3],
                    1 => [4, 7]
                ],
                '-',
                '+--+---+',
            )
        ];

        yield 'white prefixed grid table normal line' => [
            '   +--+---+',
            new TableSeparatorLineConfig(
                false,
                TableRule::TYPE_PRETTY,
                [
                    0 => [1, 3],
                    1 => [4, 7]
                ],
                '-',
                '+--+---+',
            )
        ];

        yield 'grid join start, not a table line' => [
            '+ some line',
            null
        ];

        yield 'Odd textline ' => [
            '+========',
            null
        ];

        yield 'Odd textline 2' => [
            '========',
            null
        ];

        yield 'Odd textline 3' => [
            '+===/====',
            null
        ];


        yield 'Text line' => [
            'Just text some line',
            null
        ];

        yield 'Empty line' => [
            '',
            null
        ];
    }

    public function malformedTableLineProvider()
    {
        yield ['----+-----', null];
        yield ['+====+----+', null];
        yield ['++====+===+', null];
        yield ['+----+----', null];
        yield ['+====+====', null];
        yield ['== ==+====', null];
        yield ['====+== ==', null];
    }
}
