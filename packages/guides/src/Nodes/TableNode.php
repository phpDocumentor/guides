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

use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;

use function count;
use function max;

/** @extends CompoundNode<Node> */
final class TableNode extends CompoundNode implements HasAttachedNodes
{
    /**
     * @param TableRow[] $data
     * @param TableRow[] $headers
     * @param int[] $columnWidth
     */
    public function __construct(protected array $data, protected array $headers = [], protected array $columnWidth = [])
    {
        parent::__construct();
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
     * The cells of the header rows and then of the body rows, as
     * "header-<row>-<column>" and "row-<row>-<column>".
     *
     * @return array<string, TableColumn>
     */
    public function getAttachedNodes(): array
    {
        $nodes = [];
        foreach (['header' => $this->headers, 'row' => $this->data] as $prefix => $rows) {
            foreach ($rows as $rowIndex => $row) {
                foreach ($row->getColumns() as $columnIndex => $column) {
                    $nodes[$prefix . '-' . $rowIndex . '-' . $columnIndex] = $column;
                }
            }
        }

        return $nodes;
    }
}
