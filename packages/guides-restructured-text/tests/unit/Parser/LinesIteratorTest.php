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

use function count;

final class LinesIteratorTest extends TestCase
{
    #[DataProvider('documentProvider')]
    public function testLoad(string $document, bool $preserveSpace, int $expectedCount, int $expectedLeadingLinesRemoved): void
    {
        $iterator = new LinesIterator();
        $iterator->load($document, $preserveSpace);

        self::assertCount($expectedCount, $iterator);
        self::assertSame($expectedCount, count($iterator->toArray()));
        self::assertSame($expectedLeadingLinesRemoved, $iterator->getLeadingLinesRemoved());
    }

    /** @return array<string, array{string, bool, int, int}> */
    public static function documentProvider(): array
    {
        return [
            'single line' => ['first', false, 1, 0],
            'leading empty lines' => ["\n\nfirst\nsecond", false, 2, 2],
            'leading whitespace-only line' => ["  \nfirst", false, 1, 1],
            'trailing empty lines' => ["first\nsecond\n\n", false, 2, 0],
            'windows line endings' => ["\r\n\r\nfirst\r\nsecond", false, 2, 2],
            'preserve space, leading empty lines' => ["\n\n  first\nsecond", true, 2, 2],
            'preserve space, keeps whitespace-only line' => ["  \nfirst", true, 2, 0],
            'empty document' => ['', false, 1, 0],
            'whitespace-only document' => ["  \n\n  ", false, 1, 0],
        ];
    }
}
