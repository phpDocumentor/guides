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

    public function testTildeIsNbspByDefault(): void
    {
        $lexer = new InlineLexer();
        $lexer->setInput('~');
        $lexer->moveNext();
        $lexer->moveNext();
        assertEquals(InlineLexer::NBSP, $lexer->token?->type);
    }

    public function testTildeIsWordWhenLegacyTildeDisabled(): void
    {
        $lexer = new InlineLexer(disableLegacyTilde: true);
        $lexer->setInput('~');
        $lexer->moveNext();
        $lexer->moveNext();
        assertEquals(InlineLexer::WORD, $lexer->token?->type);
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
                [InlineLexer::BACKSLASH],
            ],
        ];
    }

    #[DataProvider('hyperlinkProvider')]
    public function testHyperlinkEndsBeforeParenthesis(string $url): void
    {
        self::assertSame(
            [
                ['(text', InlineLexer::WORD],
                [' ', InlineLexer::WHITESPACE],
                ['in', InlineLexer::WORD],
                [' ', InlineLexer::WHITESPACE],
                ['parenthesis', InlineLexer::WORD],
                [' ', InlineLexer::WHITESPACE],
                [$url, InlineLexer::HYPERLINK],
                [').', InlineLexer::WORD],
            ],
            self::tokenize('(text in parenthesis ' . $url . ').'),
        );
    }

    public function testTextBetweenSpecialCharactersIsASingleToken(): void
    {
        self::assertSame(
            [
                ['Hello', InlineLexer::WORD],
                [' ', InlineLexer::WHITESPACE],
                ['|', InlineLexer::VARIABLE_DELIMITER],
                ['var', InlineLexer::WORD],
                ['|', InlineLexer::VARIABLE_DELIMITER],
                ['~', InlineLexer::NBSP],
                ['[', InlineLexer::ANNOTATION_START],
                ['#', InlineLexer::OCTOTHORPE],
                ['note', InlineLexer::WORD],
                [']', InlineLexer::ANNOTATION_END],
                ['_', InlineLexer::UNDERSCORE],
            ],
            self::tokenize('Hello |var|~[#note]_'),
        );
    }

    /** @return list<array{string, int|null}> */
    private static function tokenize(string $input): array
    {
        $lexer = new InlineLexer();
        $lexer->setInput($input);
        $lexer->moveNext();
        $lexer->moveNext();

        $tokens = [];
        while ($lexer->token !== null) {
            $tokens[] = [$lexer->token->value, $lexer->token->type];
            $lexer->moveNext();
        }

        return $tokens;
    }

    /** @return array<string, array<string>> */
    public static function hyperlinkProvider(): array
    {
        return [
            'Url with parenthesis' => ['https://www.test.com'],
            'Url with parenthesis and query' => ['https://www.test.com?query=1'],
            'Url with parenthesis and query and fragment' => ['https://www.test.com?query=1#fragment'],
        ];
    }
}
