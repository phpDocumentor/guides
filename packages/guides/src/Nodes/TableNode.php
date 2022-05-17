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

use phpDocumentor\Guides\Nodes\Table\TableRow;

use function count;
use function max;

class TableNode extends Node
{
    /** @var TableRow[] */
    protected $data = [];

    /** @var bool[] */
    protected $headers = [];

    public function __construct($rows, $headers)
    {
        parent::__construct();
        $this->data = $rows;
        $this->headers = $headers;
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

    /**
     * @return TableRow[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns an of array of which rows should be headers,
     * where the row index is the key of the array and
     * the value is always true.
     *
     * @return bool[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
