<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class SimpleTableRuleTest extends TestCase
{
    use ProphecyTrait;

    /** @dataProvider simpleTableStartProvider */
    public function testApplies(string $input): void
    {
        $parser = $this->givenDocumentParserContext($input);

        $rule = new SimpleTableRule();
        self::assertTrue($rule->applies($parser->reveal()));
    }

    public function simpleTableStartProvider(): array
    {
        return [
            ['== =='],
            ['== ==== ======='],
            ['==   ====    ======='],
            ['   ==   ====    ======='],
            ['======   ====    ======='],
        ];
    }

    /** @dataProvider nonSimpleTableStartProvider */
    public function testDoesNotApply(string $input): void
    {
        $parser = $this->givenDocumentParserContext($input);

        $rule = new SimpleTableRule();
        self::assertFalse($rule->applies($parser->reveal()));
    }

    private function givenDocumentParserContext(string $input)
    {
        $iterator = new LinesIterator();
        $iterator->load($input);

        $context = $this->prophesize(DocumentParserContext::class);
        $context->getDocumentIterator()->willReturn($iterator);
        return $context;
    }

    public function nonSimpleTableStartProvider(): array
    {
        return [
            ['+==+==+'],
            ['==+==== ======='],
            ['==   ====    =======--'],
        ];
    }


    public function testApplyReturns2ColumnTableWithoutHeader(): void
    {
        $input = <<<RST
===  ===
AAA  BBB
C    D
===  ===
RST;

        $row1 = new TableRow();
        $row1->addColumn(new TableColumn('AAA', 1, new SpanNode('AAA')));
        $row1->addColumn(new TableColumn('BBB', 1, new SpanNode('BBB')));

        $row2 = new TableRow();
        $row2->addColumn(new TableColumn('C', 1, new SpanNode('C')));
        $row2->addColumn(new TableColumn('D', 1, new SpanNode('D')));

        $expected = new TableNode(
            [
                $row1,
                $row2
            ],
            []
        );

        $rule = new SimpleTableRule();
        $result = $rule->apply($this->givenDocumentParserContext($input)->reveal(), null);

        self::assertEquals($expected, $result);
    }

    public function testApplyReturns2ColumnTableWithMultiLineCells(): void
    {
        $input = <<<RST
===  ===
AAA  BBB
     BBB
C    D
===  ===
RST;

        $row1 = new TableRow();
        $row1->addColumn(new TableColumn('AAA', 1, new SpanNode('AAA')));
        $row1->addColumn(new TableColumn("BBB\nBBB" , 1, new SpanNode("BBB\nBBB")));

        $row2 = new TableRow();
        $row2->addColumn(new TableColumn('C', 1, new SpanNode('C')));
        $row2->addColumn(new TableColumn('D', 1, new SpanNode('D')));

        $expected = new TableNode(
            [
                $row1,
                $row2
            ],
            []
        );

        $rule = new SimpleTableRule();
        $result = $rule->apply($this->givenDocumentParserContext($input)->reveal(), null);

        self::assertEquals($expected, $result);
    }
}
