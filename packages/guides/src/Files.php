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

namespace phpDocumentor\Guides;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;

use function count;
use function in_array;
use function sort;

/** @implements IteratorAggregate<string> */
final class Files implements IteratorAggregate, Countable
{
    /** @var string[] */
    private array $files = [];

    public function add(string $filename): void
    {
        if (in_array($filename, $this->files, true)) {
            return;
        }

        $this->files[] = $filename;
        sort($this->files);
    }

    /** @return Iterator<string> */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->files);
    }

    public function count(): int
    {
        return count($this->files);
    }
}
