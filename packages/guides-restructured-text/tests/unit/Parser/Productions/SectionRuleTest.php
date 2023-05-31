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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\InlineNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineTokenParser;
use phpDocumentor\Guides\UrlGeneratorInterface;

final class SectionRuleTest extends RuleTestCase
{
    public function testFirstTitleOpensSection(): void
    {
        $content = <<<'RST'
#########
Title 1
#########
RST;

        $documentParser = $this->getDocumentParserContext($content);
        $spanParser = $this->getInlineTokenParserMock();

        $titleRule = new TitleRule(
            $spanParser,
        );

        $rule = new SectionRule($titleRule, new RuleContainer());

        $document = new DocumentNode('foo', 'index');

        $rule->apply($documentParser, $document);
        self::assertEquals(
            [new SectionNode(new TitleNode(InlineNode::getPlainTextInlineNode('Title 1'), 1, 'title-1'))],
            $document->getNodes(),
        );
    }

    public function testSecondLevelTitleOpensChildSection(): void
    {
        $content = <<<'RST'
#########
Title 1
#########

Title 1.1
=========
RST;

        $documentParser = $this->getDocumentParserContext($content);
        $spanParser = $this->getInlineTokenParserMock();

        $titleRule = new TitleRule(
            $spanParser,
        );

        $rule = new SectionRule($titleRule, new RuleContainer());

        $document = new DocumentNode('foo', 'index');

        $rule->apply($documentParser, $document);

        $section = new SectionNode(new TitleNode(InlineNode::getPlainTextInlineNode('Title 1'), 1, 'title-1'));
        $section->addChildNode(new SectionNode(new TitleNode(InlineNode::getPlainTextInlineNode('Title 1.1'), 2, 'title-1-1')));

        self::assertEquals(
            [$section],
            $document->getNodes(),
        );
    }

    public function testSameLevelSectionsAreAddedAtTheSameLevel(): void
    {
        $content = <<<'RST'
#########
Title 1
#########

Title 1.1
=========

Title 1.2
=========
RST;

        $documentParser = $this->getDocumentParserContext($content);
        $spanParser = $this->getInlineTokenParserMock();

        $titleRule = new TitleRule(
            $spanParser,
        );

        $rule = new SectionRule($titleRule, new RuleContainer());

        $document = new DocumentNode('foo', 'index');

        $rule->apply($documentParser, $document);

        $section = new SectionNode(new TitleNode(InlineNode::getPlainTextInlineNode('Title 1'), 1, 'title-1'));
        $section->addChildNode(new SectionNode(new TitleNode(InlineNode::getPlainTextInlineNode('Title 1.1'), 2, 'title-1-1')));
        $section->addChildNode(new SectionNode(new TitleNode(InlineNode::getPlainTextInlineNode('Title 1.2'), 2, 'title-1-2')));

        self::assertEquals(
            [$section],
            $document->getNodes(),
        );
    }

    public function testSameLevelSectionsAreAddedAtTheSameLevel2(): void
    {
        $content = <<<'RST'
#########
Title 1
#########

Title 1.1
=========

Title 1.1.1
^^^^^^^^^^^

Title 1.2
=========

#########
Title 2
#########

#########
Title 3
#########

RST;

        $documentParser = $this->getDocumentParserContext($content);
        $inlineTokenParser = $this->getInlineTokenParserMock();

        $titleRule = new TitleRule(
            $inlineTokenParser,
        );

        $rule = new SectionRule($titleRule, new RuleContainer());

        $document = new DocumentNode('foo', 'index');

        $rule->apply($documentParser, $document);

        $section = new SectionNode(new TitleNode(InlineNode::getPlainTextInlineNode('Title 1'), 1, 'title-1'));
        $subSection = new SectionNode(new TitleNode(InlineNode::getPlainTextInlineNode('Title 1.1'), 2, 'title-1-1'));
        $section->addChildNode($subSection);
        $subSection->addChildNode(new SectionNode(new TitleNode(InlineNode::getPlainTextInlineNode('Title 1.1.1'), 3, 'title-1-1-1')));
        $section->addChildNode(new SectionNode(new TitleNode(InlineNode::getPlainTextInlineNode('Title 1.2'), 2, 'title-1-2')));
        $section2 = new SectionNode(new TitleNode(InlineNode::getPlainTextInlineNode('Title 2'), 1, 'title-2'));
        $section3 = new SectionNode(new TitleNode(InlineNode::getPlainTextInlineNode('Title 3'), 1, 'title-3'));

        self::assertEquals(
            [$section, $section2, $section3],
            $document->getNodes(),
        );
    }

    private function getInlineTokenParserMock(): InlineTokenParser
    {
        $inlineTokenParser = $this->createMock(InlineTokenParser::class);
        $inlineTokenParser->method('parse')->willReturnCallback(
            static fn (string $arg): InlineNode => InlineNode::getPlainTextInlineNode($arg)
        );

        return $inlineTokenParser;
    }

    private function getDocumentParserContext(string $content): DocumentParserContext
    {
        $parserContext = new ParserContext(
            new ProjectNode(),
            'foo',
            'test',
            1,
            $this->createStub(FilesystemInterface::class),
            $this->createStub(UrlGeneratorInterface::class),
        );

        return new DocumentParserContext(
            $content,
            $parserContext,
            $this->createStub(MarkupLanguageParser::class),
        );
    }
}
