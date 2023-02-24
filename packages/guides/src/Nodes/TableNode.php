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

/**
 * @extends CompoundNode<Node>
 */
class TableNode extends CompoundNode
{
    /** @var TableRow[] */
    protected array $data = [];

    /** @var TableRow[] */
    protected array $headers = [];

    /**
     * @param TableRow[] $rows
     * @param TableRow[] $headers
     */
    public function __construct(array $rows, array $headers)
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
     * @return TableRow[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
