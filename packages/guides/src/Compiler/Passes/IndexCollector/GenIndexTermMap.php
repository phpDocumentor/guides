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

namespace phpDocumentor\Guides\Compiler\Passes\IndexCollector;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * Every genindex term collected project-wide, keyed by its normalized term
 * text. Passed between {@see IndexEntryCollector}, {@see GenIndexSeeResolver},
 * {@see GenIndexTermMapFilter} and {@see GenIndexNodeBuilder} as they collect,
 * resolve, scope and render it in turn.
 *
 * @implements IteratorAggregate<string, GenIndexTermData>
 */
final class GenIndexTermMap implements IteratorAggregate
{
    /** @var array<string, GenIndexTermData> */
    private array $terms = [];

    public function isEmpty(): bool
    {
        return $this->terms === [];
    }

    public function get(string $key): GenIndexTermData|null
    {
        return $this->terms[$key] ?? null;
    }

    public function set(string $key, GenIndexTermData $term): void
    {
        $this->terms[$key] = $term;
    }

    public function getOrCreateTerm(string $key, string $term): GenIndexTermData
    {
        return $this->terms[$key] ??= new GenIndexTermData($term);
    }

    /** @return Traversable<string, GenIndexTermData> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->terms);
    }
}
