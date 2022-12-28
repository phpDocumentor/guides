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
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LineDataParser;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;
use phpDocumentor\Guides\UrlGenerator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class TableRuleTest extends TestCase
{
    use ProphecyTrait;

    /**
     * First 2 simple table cases are broken, headers are not detected correctly?
     *
     * @dataProvider simpleTableProvider
     * @dataProvider prettyTableBasicsProvider
     * @dataProvider gridTableWithColSpanProvider
     * @dataProvider gridTableWithRowSpanProvider
     * @dataProvider gridTableFollowUpTextProvider
     */
    public function testSimpleTableCreation(string $input, array $rows, $headers): void
    {
        $context = $this->givenDocumentParserContext($input);
        $rule = new TableRule(new LineDataParser(new SpanParser()));
        $table = $rule->apply($context->reveal());

        self::assertEquals($rows, $table->getData());
        self::assertEquals(count($rows), $table->getRows());
        self::assertEquals(count(current($rows)->getColumns()), $table->getCols());
        self::assertEquals($headers, array_keys($table->getHeaders()));
    }

    public function simpleTableProvider(): \Generator
    {
        $input = <<<RST
=====  ===== =====
Col A  Col B Col C
=====  ===== =====
Col X  Col Y Col Z
-----  ----- -----
Col U  Col J Col K
=====  ===== =====

RST;

        $row1 = new TableRow();
        $row1->addColumn($this->createColumnNode('Col A'));
        $row1->addColumn($this->createColumnNode('Col B'));
        $row1->addColumn($this->createColumnNode('Col C'));

        $row2 = new TableRow();
        $row2->addColumn($this->createColumnNode('Col X'));
        $row2->addColumn($this->createColumnNode('Col Y'));
        $row2->addColumn($this->createColumnNode('Col Z'));

        $row3 = new TableRow();
        $row3->addColumn($this->createColumnNode('Col U'));
        $row3->addColumn($this->createColumnNode('Col J'));
        $row3->addColumn($this->createColumnNode('Col K'));

        $expected = [
            2 => $row1,
            4 => $row2,
            6 => $row3,
        ];

        yield [$input, $expected, [2]];

        $input = <<<RST
=====  ===== =====
  1      2     3
=====  ===== =====
Col A  Col B Col C
Col X  Col Y Col Z
Col U  Col J Col K
=====  ===== =====

RST;
        $header1 = new TableRow();
        $header1->addColumn($this->createColumnNode('1'));
        $header1->addColumn($this->createColumnNode('2'));
        $header1->addColumn($this->createColumnNode('3'));

        $expected = [
            2 => $header1,
            3 => $row1,
            4 => $row2,
            5 => $row3,
        ];

        yield [$input, $expected, [2]];

        $input = <<<RST
=====  ===== =====
  1      2     3
Col A  Col B Col C
Col X  Col Y Col Z
Col U  Col J Col K
=====  ===== =====
RST;

        yield [$input, $expected, []];
    }

    private function createColumnNode(string $content, int $colSpan = 1): TableColumn
    {
        return new TableColumn($content, $colSpan, new SpanNode($content));
    }

    public function prettyTableBasicsProvider(): \Generator
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
        $headerRow->addColumn($this->createColumnNode('Property'));
        $headerRow->addColumn($this->createColumnNode('Data Type'));

        $row1 = new TableRow();
        $row1->addColumn($this->createColumnNode('description'));
        $row1->addColumn($this->createColumnNode('string'));

        $row2 = new TableRow();
        $row2->addColumn($this->createColumnNode('author'));
        $row2->addColumn($this->createColumnNode('string'));

        $row3 = new TableRow();
        $row3->addColumn($this->createColumnNode('keywords'));
        $row3->addColumn($this->createColumnNode('string'));

        $expected = [
            2 => $headerRow,
            4 => $row1,
            6 => $row2,
            8 => $row3
        ];

        yield [$input, $expected, [2]];

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

        yield [$input, $expected, []];
    }

    public function gridTableWithColSpanProvider(): \Generator
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
        $headerRow->addColumn($this->createColumnNode("Header row, column 1\n(header rows optional)"));
        $headerRow->addColumn($this->createColumnNode('Header 2'));
        $headerRow->addColumn($this->createColumnNode('Header 3'));
        $headerRow->addColumn($this->createColumnNode('Header 4'));

        $row1 = new TableRow();
        $row1->addColumn($this->createColumnNode('body row 1, column 1'));
        $row1->addColumn($this->createColumnNode('column 2'));
        $row1->addColumn($this->createColumnNode('column 3'));
        $row1->addColumn($this->createColumnNode('column 4'));

        $row2 = new TableRow();
        $row2->addColumn($this->createColumnNode('body row 2'));
        $row2->addColumn($this->createColumnNode('Cells may span columns.', 3));

        $expected = [
            2 => $headerRow,
            5 => $row1,
            7 => $row2
        ];

        yield [$input, $expected, [2]];

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
        $row2->addColumn($this->createColumnNode('body row 2'));
        $row2->addColumn($this->createColumnNode('Cells may span columns.', 2));
        $row2->addColumn($this->createColumnNode('column 4'));

        $expected = [
            2 => $headerRow,
            5 => $row1,
            7 => $row2
        ];

        yield [$input, $expected, [2]];
    }

    public function gridTableWithRowSpanProvider(): \Generator
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
        $headerRow->addColumn($this->createColumnNode('Property'));
        $headerRow->addColumn($this->createColumnNode('Data Type'));

        $row1 = new TableRow();
        $row1->addColumn($this->createColumnNode('description'));
        $rowSpan = $this->createColumnNode('string');
        $rowSpan->incrementRowSpan();
        $row1->addColumn($rowSpan);

        $row2 = new TableRow();
        $row2->addColumn($this->createColumnNode('author'));

        $row3 = new TableRow();
        $row3->addColumn($this->createColumnNode('keywords'));
        $row3->addColumn($this->createColumnNode('string'));

        $expected = [
            2 => $headerRow,
            4 => $row1,
            6 => $row2,
            8 => $row3
        ];

        yield [$input, $expected, [2]];
    }

    public function gridTableFollowUpTextProvider(): \Generator
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
        $headerRow->addColumn($this->createColumnNode('Property'));
        $headerRow->addColumn($this->createColumnNode('Data Type'));

        $row3 = new TableRow();
        $row3->addColumn($this->createColumnNode('keywords'));
        $row3->addColumn($this->createColumnNode('string'));

        $expected = [
            2 => $headerRow,
            4 => $row3
        ];

        yield [$input, $expected, [2]];
    }

    //Add error cases with invalid table formats

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

        $context = $this->givenDocumentParserContext($input);
        $rule = new TableRule(new LineDataParser(new SpanParser()));
        $rule->apply($context->reveal());

        self::assertContainsError(
            <<<'ERROR'
Malformed table: Line

| keywords                          | string

does not appear to be a complete table row
in file test

+-----------------------------------+---------------+
+-----------------------------------+---------------+
| Property                          | Data Type     |
+===================================+===============+
| description                       | string        |
+-----------------------------------+               |
| author                            |               |
+-----------------------------------+---------------+
| keywords                          | string
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

        $context = $this->givenDocumentParserContext($input);
        $rule = new TableRule(new LineDataParser(new SpanParser()));
        $rule->apply($context->reveal());

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

        $context = $this->givenDocumentParserContext($input);
        $rule = new TableRule(new LineDataParser(new SpanParser()));
        $rule->apply($context->reveal());

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

    private function getParserContext(): ParserContext
    {
        return new ParserContext(
            'test',
            '/test',
            1,
            $this->prophesize(FilesystemInterface::class)->reveal(),
            $this->prophesize(UrlGenerator::class)->reveal()
        );
    }

    private function givenDocumentParserContext(string $input)
    {
        $iterator = new LinesIterator();
        $iterator->load($input);

        $context = $this->prophesize(DocumentParserContext::class);
        $context->getDocumentIterator()->willReturn($iterator);
        return $context;
    }

    private static function assertContainsError(string $error, ParserContext $context): void
    {
        self::assertContains($error, $context->getErrors());
    }
}
