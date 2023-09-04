<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

class InlineLexerTest extends TestCase
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
        ];
    }
}
