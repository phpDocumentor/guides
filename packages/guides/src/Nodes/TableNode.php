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

use function array_merge;
use function count;
use function max;
use function sprintf;

/**
 * The cells of the header rows and then of the data rows are the children of
 * the table, so that the compiler processes their content, such as the
 * directives that are created at compile time.
 *
 * @extends CompoundNode<TableColumn>
 */
final class TableNode extends CompoundNode
{
    /**
     * @param TableRow[] $data
     * @param TableRow[] $headers
     * @param int[] $columnWidth
     */
    public function __construct(protected array $data, protected array $headers = [], protected array $columnWidth = [])
    {
        parent::__construct($this->columnsOf(array_merge($this->headers, $this->data)));
    }

    public function getCols(): int
    {
        $columns = 0;
        foreach ($this->data as $row) {
            $columns = max($columns, count($row->getColumns()));
        }

        return $columns;
    }

    public function getRows(): int
    {
        return count($this->data);
    }

    /** @return TableRow[] */
    public function getData(): array
    {
        return $this->data;
    }

    /** @return TableRow[] */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /** @return int[] */
    public function getColumnWidth(): array
    {
        return $this->columnWidth;
    }

    /** @param int[] $columnWidth */
    public function withColumnWidth(array $columnWidth): TableNode
    {
        $table = clone $this;
        $table->columnWidth = $columnWidth;

        return $table;
    }

    /**
     * @param TableColumn $node
     *
     * @return static
     */
    public function replaceNode(int $key, Node $node): self
    {
        if ($node instanceof TableColumn === false) {
            throw new LogicException(sprintf('A cell of a table can only be replaced by a %s.', TableColumn::class));
        }

        $table = clone $this;
        $position = 0;
        $table->headers = $this->replaceColumn($this->headers, $key, $node, $position);
        $table->data = $this->replaceColumn($this->data, $key, $node, $position);
        $table->value = $this->columnsOf(array_merge($table->headers, $table->data));

        return $table;
    }

    /**
     * A table keeps its shape: a removed cell becomes an empty cell of the
     * same size.
     *
     * @return static
     */
    public function removeNode(int $key): self
    {
        $column = $this->value[$key] ?? null;
        if ($column === null) {
            return clone $this;
        }

        return $this->replaceNode($key, new TableColumn('', $column->getColSpan(), [], $column->getRowSpan()));
    }

    /**
     * @param TableRow[] $rows
     *
     * @return TableColumn[]
     */
    private function columnsOf(array $rows): array
    {
        $columns = [];
        foreach ($rows as $row) {
            foreach ($row->getColumns() as $column) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * @param TableRow[] $rows
     *
     * @return TableRow[]
     */
    private function replaceColumn(array $rows, int $key, TableColumn $node, int &$position): array
    {
        $result = [];
        foreach ($rows as $rowKey => $row) {
            $columns = [];
            foreach ($row->getColumns() as $columnKey => $column) {
                $columns[$columnKey] = $position === $key ? $node : $column;
                $position++;
            }

            $result[$rowKey] = new TableRow($columns);
        }

        return $result;
    }
}
