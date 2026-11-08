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

use function array_key_exists;
use function count;
use function sort;

use const SORT_FLAG_CASE;
use const SORT_NATURAL;

/** @implements IteratorAggregate<string> */
final class Files implements IteratorAggregate, Countable
{
    /** @var string[] */
    private array $files = [];

    /** @var array<string, string> */
    private array $extensions = [];

    public function add(string $filename, string|null $extension = null): void
    {
        if (array_key_exists($filename, $this->files)) {
            return;
        }

        $this->files[$filename] = $filename;
        if ($extension === null) {
            return;
        }

        $this->extensions[$filename] = $extension;
    }

    /**
     * The extension of the file the document was found in, or null when it was added without one.
     */
    public function getExtension(string $filename): string|null
    {
        return $this->extensions[$filename] ?? null;
    }

    /** @return Iterator<string> */
    public function getIterator(): Iterator
    {
        sort($this->files, SORT_NATURAL | SORT_FLAG_CASE);

        return new ArrayIterator($this->files);
    }

    public function count(): int
    {
        return count($this->files);
    }
}
