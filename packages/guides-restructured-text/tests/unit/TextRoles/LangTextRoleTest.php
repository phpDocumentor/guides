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

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use phpDocumentor\Guides\Nodes\Inline\LanguageInlineNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use PHPUnit\Framework\TestCase;

final class LangTextRoleTest extends TestCase
{
    private TestHandler $logHandler;
    private LangTextRole $subject;
    private DocumentParserContext $documentParserContext;

    protected function setUp(): void
    {
        $this->logHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($this->logHandler);
        $this->subject = new LangTextRole($logger);
        $this->documentParserContext = self::createMock(DocumentParserContext::class);
        $this->documentParserContext->method('getContext')->willReturn(
            self::createMock(ParserContext::class),
        );
    }

    public function testGetNameReturnsLang(): void
    {
        self::assertSame('lang', $this->subject->getName());
    }

    public function testLanguageOnlyIsParsed(): void
    {
        $inline = $this->subject->processNode($this->documentParserContext, 'lang', 'مثال عربي (ar)', 'مثال عربي (ar)');

        self::assertInstanceOf(LanguageInlineNode::class, $inline);
        self::assertSame('ar', $inline->getLanguage());
        self::assertNull($inline->getDirection());
        self::assertSame('مثال عربي', $inline->getContent());
        self::assertFalse($this->logHandler->hasWarningRecords());
    }

    public function testLanguageAndDirectionAreParsed(): void
    {
        $inline = $this->subject->processNode($this->documentParserContext, 'lang', 'مثال عربي (ar, rtl)', 'مثال عربي (ar, rtl)');

        self::assertInstanceOf(LanguageInlineNode::class, $inline);
        self::assertSame('ar', $inline->getLanguage());
        self::assertSame('rtl', $inline->getDirection());
        self::assertSame('مثال عربي', $inline->getContent());
        self::assertFalse($this->logHandler->hasWarningRecords());
    }

    public function testMissingLanguageWarns(): void
    {
        $inline = $this->subject->processNode($this->documentParserContext, 'lang', 'no language here', 'no language here');

        self::assertInstanceOf(LanguageInlineNode::class, $inline);
        self::assertNull($inline->getLanguage());
        self::assertTrue($this->logHandler->hasWarningThatContains(
            'The "lang" role requires a language.',
        ));
    }

    public function testInvalidDirectionWarnsButIsStillApplied(): void
    {
        $inline = $this->subject->processNode($this->documentParserContext, 'lang', 'text (ar, sideways)', 'text (ar, sideways)');

        self::assertInstanceOf(LanguageInlineNode::class, $inline);
        self::assertSame('sideways', $inline->getDirection());
        self::assertTrue($this->logHandler->hasWarningThatContains(
            'expects the direction to be one of "ltr", "rtl" or "auto", but was given "sideways"',
        ));
    }

    public function testInvalidLanguageWarnsButIsStillApplied(): void
    {
        $inline = $this->subject->processNode($this->documentParserContext, 'lang', 'text (not a language)', 'text (not a language)');

        self::assertInstanceOf(LanguageInlineNode::class, $inline);
        self::assertSame('not a language', $inline->getLanguage());
        self::assertTrue($this->logHandler->hasWarningThatContains(
            'expects a BCP 47 language tag (e.g. "en", "en-US"), but was given "not a language"',
        ));
    }
}
