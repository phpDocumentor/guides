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

namespace phpDocumentor\Guides\Nodes;

use LogicException;
use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TableNode::class)]
final class TableNodeTest extends TestCase
{
    public function testTheCellsOfTheHeaderAndThenOfTheDataAreTheChildren(): void
    {
        $header = new TableColumn('Header', 1);
        $first = new TableColumn('First', 1);
        $second = new TableColumn('Second', 1);
        $table = new TableNode([new TableRow([$first]), new TableRow([$second])], [new TableRow([$header])]);

        self::assertSame([$header, $first, $second], $table->getChildren());
    }

    public function testAReplacedCellIsReplacedInItsRow(): void
    {
        $header = new TableColumn('Header', 1);
        $first = new TableColumn('First', 1);
        $second = new TableColumn('Second', 1);
        $table = new TableNode([new TableRow([$first, $second])], [new TableRow([$header])]);
        $replacement = new TableColumn('Replacement', 1, [new RawNode('replacement')]);

        $replaced = $table->replaceNode(2, $replacement);

        self::assertSame([$first, $replacement], $replaced->getData()[0]->getColumns());
        self::assertSame([$header], $replaced->getHeaders()[0]->getColumns());
        self::assertSame([$header, $first, $replacement], $replaced->getChildren());
        self::assertSame([$first, $second], $table->getData()[0]->getColumns());
    }

    public function testARemovedCellBecomesAnEmptyCellOfTheSameSize(): void
    {
        $first = new TableColumn('First', 2, [new RawNode('first')], 3);
        $table = new TableNode([new TableRow([$first])]);

        $removed = $table->removeNode(0);

        $column = $removed->getData()[0]->getFirstColumn();
        self::assertSame([], $column->getChildren());
        self::assertSame(2, $column->getColSpan());
        self::assertSame(3, $column->getRowSpan());
    }

    public function testACellCanOnlyBeReplacedByACell(): void
    {
        $table = new TableNode([new TableRow([new TableColumn('First', 1)])]);

        $this->expectException(LogicException::class);

        /** @phpstan-ignore argument.type (the check that this test is about) */
        $table->replaceNode(0, new RawNode('not a cell'));
    }
}
