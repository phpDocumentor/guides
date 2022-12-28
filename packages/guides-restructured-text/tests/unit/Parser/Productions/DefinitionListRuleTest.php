<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LineDataParser;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class DefinitionListRuleTest extends TestCase
{
    use ProphecyTrait;

    public function testAppliesReturnsTrueOnValidInput(): void
    {
        $documentIterator = new LinesIterator();
        $documentIterator->load(<<<RST
term 1
    Definition 1

term 2
    Definition 1

    Definition 2

    Definition 3
RST
        );

        $documentParser = $this->prophesize(DocumentParserContext::class);
        $documentParser->getDocumentIterator()->willReturn($documentIterator);

        $rule = new DefinitionListRule(
            $this->prophesize(SpanParser::class)->reveal()
        );

        self::assertTrue($rule->applies($documentParser->reveal()));
    }

    public function testAppliesReturnsFalse(): void
    {
        $documentIterator = new LinesIterator();
        $documentIterator->load(<<<RST
term 1
Definition 1

term 2
    Definition 1

    Definition 2

    Definition 3
RST
        );

        $documentParser = $this->prophesize(DocumentParserContext::class);
        $documentParser->getDocumentIterator()->willReturn($documentIterator);

        $rule = new DefinitionListRule(
            $this->prophesize(SpanParser::class)->reveal()
        );

        self::assertFalse($rule->applies($documentParser->reveal()));
    }
}
