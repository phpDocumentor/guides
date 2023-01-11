<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class SimpleTableRuleTest extends AbstractRuleTest
{
    use ProphecyTrait;

    private SimpleTableRule $rule;

    protected function setUp(): void
    {
        $this->rule = new SimpleTableRule($this->givenCollectAllRuleContainer());
    }


    /** @dataProvider simpleTableStartProvider */
    public function testApplies(string $input): void
    {
        $parser = $this->createContext($input);

        self::assertTrue($this->rule->applies($parser));
    }

    /** @return string[][] */
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
        $parser = $this->createContext($input);

        self::assertFalse($this->rule->applies($parser));
    }

    /** @return string[][] */
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
        $row1->addColumn($this->createColumn('AAA'));
        $row1->addColumn($this->createColumn('BBB'));

        $row2 = new TableRow();
        $row2->addColumn($this->createColumn('C'));
        $row2->addColumn($this->createColumn('D'));

        $expected = new TableNode(
            [
                $row1,
                $row2
            ],
            []
        );

        $result = $this->rule->apply($this->createContext($input), null);

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
        $row1->addColumn($this->createColumn('AAA'));
        $row1->addColumn($this->createColumn("BBB\nBBB"));

        $row2 = new TableRow();
        $row2->addColumn($this->createColumn('C'));
        $row2->addColumn($this->createColumn('D'));

        $expected = new TableNode(
            [
                $row1,
                $row2
            ],
            []
        );

        $result = $this->rule->apply($this->createContext($input), null);

        self::assertEquals($expected, $result);
    }

    public function testApplyReturns3ColumnTableWithHeader(): void
    {
        $input = <<<RST
=========== ========== ========
First col   Second col Third col
=========== ========== ========
Second row  Other col  Last col
Third row              Last col
Forth row
\           Fith row
=========== ========== ========
RST;

        $row1 = new TableRow();
        $row1->addColumn($this->createColumn('First col'));
        $row1->addColumn($this->createColumn('Second col'));
        $row1->addColumn($this->createColumn('Third col'));

        $row2 = new TableRow();
        $row2->addColumn($this->createColumn('Second row'));
        $row2->addColumn($this->createColumn('Other col'));
        $row2->addColumn($this->createColumn('Last col'));

        $row3 = new TableRow();
        $row3->addColumn($this->createColumn('Third row'));
        $row3->addColumn($this->createColumn(''));
        $row3->addColumn($this->createColumn('Last col'));

        $row4 = new TableRow();
        $row4->addColumn($this->createColumn('Forth row'));
        $row4->addColumn($this->createColumn(''));
        $row4->addColumn($this->createColumn(''));

        $row5 = new TableRow();
        $row5->addColumn($this->createColumn(''));
        $row5->addColumn($this->createColumn('Fith row'));
        $row5->addColumn($this->createColumn(''));

        $expected = new TableNode(
            [
                $row2,
                $row3,
                $row4,
                $row5,
            ],
            [
                $row1,
            ]
        );

        $result = $this->rule->apply($this->createContext($input), null);

        self::assertEquals($expected, $result);
    }

    public function testApplyReturns3ColumnTableIgnoringRuler(): void
    {
        $input = <<<RST
=========== ========== ========
First col   Second col Third col
=========== ========== ========
Second row  Other col  Last col
----------  ---------  --------
Third row              Last col

Forth row
=========== ========== ========

This is not table content
RST;

        $row1 = new TableRow();
        $row1->addColumn($this->createColumn('First col'));
        $row1->addColumn($this->createColumn('Second col'));
        $row1->addColumn($this->createColumn('Third col'));

        $row2 = new TableRow();
        $row2->addColumn($this->createColumn('Second row'));
        $row2->addColumn($this->createColumn('Other col'));
        $row2->addColumn($this->createColumn('Last col'));

        $row3 = new TableRow();
        $row3->addColumn($this->createColumn('Third row'));
        $row3->addColumn($this->createColumn(''));
        $row3->addColumn($this->createColumn('Last col'));

        $row4 = new TableRow();
        $row4->addColumn($this->createColumn('Forth row'));
        $row4->addColumn($this->createColumn(''));
        $row4->addColumn($this->createColumn(''));

        $expected = new TableNode(
            [
                $row2,
                $row3,
                $row4
            ],
            [
                $row1
            ]
        );

        $content = $this->createContext($input);
        $result = $this->rule->apply($content, null);

        self::assertEquals($expected, $result);
        self::assertRemainingEquals(
            <<<RST

This is not table content

RST
            ,
            $content->getDocumentIterator()
        );
    }

    private function createColumn(string $content): TableColumn
    {
        return new TableColumn($content, 1, [new RawNode($content)]);
    }
}
