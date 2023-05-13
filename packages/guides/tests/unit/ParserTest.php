<?php

declare(strict_types=1);

namespace phpDocumentor\Guides;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Settings\SettingsManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ParserTest extends TestCase
{
    public function testParseWillThrowWhenInputFormatIsNotSupported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to parse document, no matching parsing strategy could be found');

        $parser = new Parser(
            $this->createMock(SettingsManager::class),
            $this->createMock(UrlGeneratorInterface::class),
            [$this->createMock(MarkupLanguageParser::class)],
        );

        $parser->parse('foobar', 'foo');
    }

    public function testParseWillCallMarkupLanguageParserWhenInputFormatMatched(): void
    {
        $documentNode = new DocumentNode(new ProjectNode(), 'foobar', 'rst');
        $languageParser = $this->createMock(MarkupLanguageParser::class);
        $languageParser->method('supports')->willReturn(true);
        $languageParser->expects(self::once())
            ->method('parse')
            ->willReturn(
                $documentNode,
            );

        $parser = new Parser(
            $this->createMock(SettingsManager::class),
            $this->createMock(UrlGeneratorInterface::class),
            [$languageParser],
        );

        $result = $parser->parse('foobar', 'rst');

        self::assertSame($documentNode, $result);
    }
}
