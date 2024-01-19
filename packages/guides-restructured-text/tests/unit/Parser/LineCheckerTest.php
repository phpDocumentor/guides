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

namespace phpDocumentor\Guides\RestructuredText\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LineCheckerTest extends TestCase
{
    #[DataProvider('specialLineProvider')]
    public function testSpecialLine(string $input, int $max, string|null $expected): void
    {
        self::assertEquals($expected, LineChecker::isSpecialLine($input, $max));
    }

    /** @return array<int, array<int, int|string|null>> */
    public static function specialLineProvider(): array
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
