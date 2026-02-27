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

namespace phpDocumentor\Guides\Markdown\Parsers;

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CodeBlockParserTest extends TestCase
{
    private CodeBlockParser $parser;

    protected function setUp(): void
    {
        $this->parser = new CodeBlockParser();
    }

    /** @return array<string, array{string, string}> */
    public static function languageProvider(): array
    {
        return [
            'php' => ['php', 'php'],
            'javascript' => ['javascript', 'javascript'],
            'c++' => ['c++', 'c++'],
            'first word only from info string' => ['ruby startline=1', 'ruby'],
        ];
    }

    #[DataProvider('languageProvider')]
    public function test_fenced_code_block_sets_language_from_info_string(string $info, string $expectedLanguage): void
    {
        $fencedCode = new FencedCode(3, '`', 0);
        $fencedCode->setInfo($info);
        $fencedCode->setLiteral("echo \"Hello\";\n");

        $result = $this->parser->parse(
            $this->createMock(MarkupLanguageParser::class),
            new NodeWalker($fencedCode),
            $fencedCode,
        );

        self::assertSame($expectedLanguage, $result->getLanguage());
        self::assertSame("echo \"Hello\";\n", $result->getValue());
    }

    public function test_fenced_code_block_without_info_has_null_language(): void
    {
        $fencedCode = new FencedCode(3, '`', 0);
        $fencedCode->setLiteral("some code\n");

        $result = $this->parser->parse(
            $this->createMock(MarkupLanguageParser::class),
            new NodeWalker($fencedCode),
            $fencedCode,
        );

        self::assertNull($result->getLanguage());
        self::assertSame("some code\n", $result->getValue());
    }

    public function test_fenced_code_block_with_empty_info_has_null_language(): void
    {
        $fencedCode = new FencedCode(3, '`', 0);
        $fencedCode->setInfo('');
        $fencedCode->setLiteral("some code\n");

        $result = $this->parser->parse(
            $this->createMock(MarkupLanguageParser::class),
            new NodeWalker($fencedCode),
            $fencedCode,
        );

        self::assertNull($result->getLanguage());
    }

    public function test_indented_code_block_has_null_language(): void
    {
        $indentedCode = new IndentedCode();
        $indentedCode->setLiteral("some code\n");

        $result = $this->parser->parse(
            $this->createMock(MarkupLanguageParser::class),
            new NodeWalker($indentedCode),
            $indentedCode,
        );

        self::assertNull($result->getLanguage());
        self::assertSame("some code\n", $result->getValue());
    }

    public function test_supports_fenced_code(): void
    {
        $fencedCode = new FencedCode(3, '`', 0);
        $event = new NodeWalkerEvent($fencedCode, true);

        self::assertTrue($this->parser->supports($event));
    }

    public function test_supports_indented_code(): void
    {
        $indentedCode = new IndentedCode();
        $event = new NodeWalkerEvent($indentedCode, true);

        self::assertTrue($this->parser->supports($event));
    }
}
