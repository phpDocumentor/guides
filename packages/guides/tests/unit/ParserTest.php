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

namespace phpDocumentor\Guides;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ParserTest extends TestCase
{
    public function testParseWillThrowWhenInputFormatIsNotSupported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to parse document, no matching parsing strategy could be found');

        $parser = new Parser(
            $this->createMock(DocumentNameResolverInterface::class),
            [$this->createMock(MarkupLanguageParser::class)],
        );

        $parser->parse('foobar', 'foo');
    }

    public function testParseWillCallMarkupLanguageParserWhenInputFormatMatched(): void
    {
        $documentNode = new DocumentNode('foobar', 'rst');
        $languageParser = $this->createMock(MarkupLanguageParser::class);
        $languageParser->method('supports')->willReturn(true);
        $languageParser->expects(self::once())
            ->method('parse')
            ->willReturn(
                $documentNode,
            );

        $parser = new Parser(
            $this->createMock(DocumentNameResolverInterface::class),
            [$languageParser],
        );

        $result = $parser->parse('foobar', 'rst');

        self::assertSame($documentNode, $result);
    }
}
