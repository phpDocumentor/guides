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

use Generator;
use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use PHPUnit\Framework\Attributes\DataProvider;

use function assert;
use function count;
use function current;

final class GridTableRuleTest extends RuleTestCase
{
    private GridTableRule $rule;

    protected function setUp(): void
    {
        $this->rule = new GridTableRule($this->givenCollectAllRuleContainer());
    }

    private static function createColumnNode(string $content, int $colSpan = 1): TableColumn
    {
        return new TableColumn($content, $colSpan, [new RawNode($content)]);
    }

    #[DataProvider('tableStartProvider')]
    public function testApplies(string $input): void
    {
        $parser = $this->createContext($input);

        self::assertTrue($this->rule->applies($parser));
    }

    /** @return string[][] */
    public static function tableStartProvider(): array
    {
        return [
            ['+--+'],
            ['+------+--+'],
            ['+---+------+-+'],
        ];
    }

    #[DataProvider('nonTableStartProvider')]
    public function testDoesNotApply(string $input): void
    {
        $parser = $this->createContext($input);

        self::assertFalse($this->rule->applies($parser));
    }

    /** @return string[][] */
    public static function nonTableStartProvider(): array
    {
        return [
            ['+==+==+'],
            ['++----+'],
            ['+---+---'],
            ['+---+---++'],
        ];
    }

    /**
     * First 2 simple table cases are broken, headers are not detected correctly?
     *
     * @param non-empty-list<TableRow> $rows
     * @param non-empty-list<TableRow> $headers
     */
    #[DataProvider('prettyTableBasicsProvider')]
    #[DataProvider('gridTableWithColSpanProvider')]
    #[DataProvider('gridTableWithRowSpanProvider')]
    public function testSimpleTableCreation(string $input, array $rows, array $headers): void
    {
        $context = $this->createContext($input);

        $table = $this->rule->apply($context);
        assert($table instanceof TableNode);

        self::assertEquals($rows, $table->getData());
        self::assertEquals(count(current($rows)->getColumns()), $table->getCols());
        self::assertEquals($headers, $table->getHeaders());
    }

    /** @return Generator<mixed[]> */
    public static function prettyTableBasicsProvider(): Generator
    {
        $input = <<<RST
+-----------------------------------+---------------+
| Property                          | Data Type     |
+===================================+===============+
| description                       | string        |
+-----------------------------------+---------------+
| author                            | string        |
+-----------------------------------+---------------+
| keywords                          | string        |
+-----------------------------------+---------------+
RST;

        $headerRow = new TableRow();
        $headerRow->addColumn(self::createColumnNode('Property'));
        $headerRow->addColumn(self::createColumnNode('Data Type'));

        $row1 = new TableRow();
        $row1->addColumn(self::createColumnNode('description'));
        $row1->addColumn(self::createColumnNode('string'));

        $row2 = new TableRow();
        $row2->addColumn(self::createColumnNode('author'));
        $row2->addColumn(self::createColumnNode('string'));

        $row3 = new TableRow();
        $row3->addColumn(self::createColumnNode('keywords'));
        $row3->addColumn(self::createColumnNode('string'));

        yield [$input, [$row1, $row2, $row3], [$headerRow]];

        $input = <<<RST
+-----------------------------------+---------------+
| Property                          | Data Type     |
+-----------------------------------+---------------+
| description                       | string        |
+-----------------------------------+---------------+
| author                            | string        |
+-----------------------------------+---------------+
| keywords                          | string        |
+-----------------------------------+---------------+
RST;

        yield [$input, [$headerRow, $row1, $row2, $row3], []];
    }

    public static function gridTableWithColSpanProvider(): Generator
    {
        $input = <<<RST
+------------------------+------------+----------+----------+
| Header row, column 1   | Header 2   | Header 3 | Header 4 |
| (header rows optional) |            |          |          |
+========================+============+==========+==========+
| body row 1, column 1   | column 2   | column 3 | column 4 |
+------------------------+------------+----------+----------+
| body row 2             | Cells may span columns.          |
+------------------------+------------+---------------------+
RST;
        $headerRow = new TableRow();
        $headerRow->addColumn(self::createColumnNode("Header row, column 1\n(header rows optional)"));
        $headerRow->addColumn(self::createColumnNode('Header 2'));
        $headerRow->addColumn(self::createColumnNode('Header 3'));
        $headerRow->addColumn(self::createColumnNode('Header 4'));

        $row1 = new TableRow();
        $row1->addColumn(self::createColumnNode('body row 1, column 1'));
        $row1->addColumn(self::createColumnNode('column 2'));
        $row1->addColumn(self::createColumnNode('column 3'));
        $row1->addColumn(self::createColumnNode('column 4'));

        $row2 = new TableRow();
        $row2->addColumn(self::createColumnNode('body row 2'));
        $row2->addColumn(self::createColumnNode('Cells may span columns.', 3));

        yield [$input, [$row1, $row2], [$headerRow]];

        $input = <<<RST
+------------------------+------------+------------+----------+
| Header row, column 1   | Header 2   | Header 3   | Header 4 |
| (header rows optional) |            |            |          |
+========================+============+============+==========+
| body row 1, column 1   | column 2   | column 3   | column 4 |
+------------------------+------------+------------+----------+
| body row 2             | Cells may span columns. | column 4 |
+------------------------+-------------------------+----------+
RST;

        $row2 = new TableRow();
        $row2->addColumn(self::createColumnNode('body row 2'));
        $row2->addColumn(self::createColumnNode('Cells may span columns.', 2));
        $row2->addColumn(self::createColumnNode('column 4'));

        yield [$input, [$row1, $row2], [$headerRow]];
    }

    public static function gridTableWithRowSpanProvider(): Generator
    {
        $input = <<<RST
+-----------------------------------+---------------+
| Property                          | Data Type     |
+===================================+===============+
| description                       | string        |
+-----------------------------------+               |
| author                            |               |
+-----------------------------------+---------------+
| keywords                          | string        |
+-----------------------------------+---------------+
RST;

        $headerRow = new TableRow();
        $headerRow->addColumn(self::createColumnNode('Property'));
        $headerRow->addColumn(self::createColumnNode('Data Type'));

        $row1 = new TableRow();
        $row1->addColumn(self::createColumnNode('description'));
        $rowSpan = self::createColumnNode('string');
        $rowSpan->incrementRowSpan();
        $row1->addColumn($rowSpan);

        $row2 = new TableRow();
        $row2->addColumn(self::createColumnNode('author'));

        $row3 = new TableRow();
        $row3->addColumn(self::createColumnNode('keywords'));
        $row3->addColumn(self::createColumnNode('string'));

        yield [$input, [$row1, $row2, $row3], [$headerRow]];
    }

    public function gridTableFollowUpTextProvider(): Generator
    {
        $input = <<<RST
+-----------------------------------+---------------+
| Property                          | Data Type     |
+===================================+===============+
| keywords                          | string        |
+-----------------------------------+---------------+

Some text
RST;

        $headerRow = new TableRow();
        $headerRow->addColumn(self::createColumnNode('Property'));
        $headerRow->addColumn(self::createColumnNode('Data Type'));

        $row3 = new TableRow();
        $row3->addColumn(self::createColumnNode('keywords'));
        $row3->addColumn(self::createColumnNode('string'));

        yield [$input, [$row3], [$headerRow]];
    }

    public function testTableNotClosed(): void
    {
        $input = <<<RST
+-----------------------------------+---------------+
| Property                          | Data Type     |
+===================================+===============+
| description                       | string        |
+-----------------------------------+               |
| author                            |               |
+-----------------------------------+---------------+
| keywords                          | string        
RST;

        $context = $this->createContext($input);
        $this->rule->apply($context);

        self::assertContainsError(
            <<<'ERROR'
Malformed table: Line

| keywords                          | string

does not appear to be a complete table row
ERROR
            ,
            $context
        );
    }

    public function testErrorMultipleHeaderRows(): void
    {
        $input = <<<RST
+-----------------------------------+---------------+
| Property                          | Data Type     |
+===================================+===============+
| description                       | string        |
+===================================+===============+
| author                            | string        |
+-----------------------------------+---------------+
| keywords                          | string        |
+-----------------------------------+---------------+
RST;

        $context = $this->createContext($input);
        $this->rule->apply($context);

        self::assertContainsError(
            <<<'ERROR'
Malformed table: multiple "header rows" using "===" were found. See table lines "3" and "5"
in file test

+-----------------------------------+---------------+
+-----------------------------------+---------------+
| Property                          | Data Type     |
+===================================+===============+
| description                       | string        |
+===================================+===============+
| author                            | string        |
+-----------------------------------+---------------+
| keywords                          | string        |
+-----------------------------------+---------------+
ERROR
            ,
            $context
        );
    }

    public function testNotEndingWithWhiteLine(): void
    {
        $this->markTestSkipped('Not correct yet');
        $input = <<<RST
+-----------------------------------+---------------+
| Property                          | Data Type     |
+===================================+===============+
| description                       | string        |
+-----------------------------------+---------------+
| author                            | string        |
+-----------------------------------+---------------+
| keywords                          | string        |
+-----------------------------------+---------------+
SOME more text here
RST;

        $context = $this->createContext($input);
        $this->rule->apply($context);

        self::assertContainsError(
            <<<'ERROR'
Malformed table: multiple "header rows" using "===" were found. See table lines "3" and "5"
in file test


+-----------------------------------+---------------+
| Property                          | Data Type     |
+===================================+===============+
| description                       | string        |
+-----------------------------------+---------------+
| author                            | string        |
+-----------------------------------+---------------+
| keywords                          | string        |
+-----------------------------------+---------------+
ERROR
            ,
            $context
        );
    }

    private static function assertContainsError(string $error, DocumentParserContext $context): void
    {
        self::assertContains($error, $context->getContext()->getErrors());
    }
}
