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

namespace phpDocumentor\Guides\Settings;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProjectSettingsTest extends TestCase
{
    /** @param list<string> $expectedFormats */
    #[DataProvider('provideInputFormats')]
    public function testInputFormatsAreReadFromACommaSeparatedList(
        string $configured,
        string $expectedFormat,
        array $expectedFormats,
    ): void {
        $settings = new ProjectSettings();
        $settings->setInputFormat($configured);

        self::assertSame($expectedFormat, $settings->getInputFormat());
        self::assertSame($expectedFormats, $settings->getInputFormats());
    }

    /** @return iterable<string, array{string, string, list<string>}> */
    public static function provideInputFormats(): iterable
    {
        yield 'one format' => ['md', 'md', ['md']];
        yield 'two formats' => ['rst,md', 'rst', ['rst', 'md']];
        yield 'spaces and repetitions' => [' rst , md ,rst,', 'rst', ['rst', 'md']];
    }
}
