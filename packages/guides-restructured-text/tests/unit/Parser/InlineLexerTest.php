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

use function PHPUnit\Framework\assertEquals;

final class InlineLexerTest extends TestCase
{
    /** @param int[] $result */
    #[DataProvider('inlineLexerProvider')]
    public function testLexer(string $input, array $result): void
    {
        $lexer = new InlineLexer();
        $lexer->setInput($input);
        $lexer->moveNext();
        $lexer->moveNext();
        foreach ($result as $tokenType) {
            assertEquals($tokenType, $lexer->token?->type);
        }
    }

    /** @return array<string, array<string | int[]>> */
    public static function inlineLexerProvider(): array
    {
        return [
            'Backtick' => [
                '`',
                [InlineLexer::BACKTICK],
            ],
            'Normal Url' => [
                'http://www.test.com',
                [InlineLexer::HYPERLINK],
            ],
            'HTTPS Url' => [
                'https://www.test.com',
                [InlineLexer::HYPERLINK],
            ],
            'Not HTTPS Url' => [
                'https:// somthing else',
                [InlineLexer::WORD],
            ],
            'Not an url' => [
                'er::anchor_',
                [InlineLexer::WORD],
            ],
            'String with underscore' => [
                'EXT:css_styled_content/static/v6.2',
                [InlineLexer::WORD],
            ],
            'Named Reference' => [
                'css_',
                [InlineLexer::NAMED_REFERENCE],
            ],
            'Named Reference in sentence' => [
                'css_ and something',
                [InlineLexer::NAMED_REFERENCE],
            ],
            'Email' => [
                'git@github.com',
                [InlineLexer::EMAIL],
            ],
            'Email in backticks' => [
                '`git@github.com`',
                [InlineLexer::BACKTICK],
            ],
            'Escaped double backtick' => [
                '\\``git@github.com`',
                [InlineLexer::ESCAPED_SIGN],
            ],
        ];
    }
}
