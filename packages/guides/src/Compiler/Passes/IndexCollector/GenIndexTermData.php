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

/**
 * All rows collected for a single genindex term or subterm, before it's
 * turned into a rendered {@see \phpDocumentor\Guides\Nodes\Index\GenIndexTerm}
 * node. Only ever nested one level deep in practice (a subterm's own
 * `subterms` stays empty), the same convention `GenIndexTerm` itself follows.
 */
final class GenIndexTermData
{
    /**
     * @param GenIndexRowData[] $rows
     * @param array<string, GenIndexTermData> $subterms
     */
    public function __construct(
        private readonly string $term,
        private array $rows = [],
        private array $subterms = [],
    ) {
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    /** @return GenIndexRowData[] */
    public function getRows(): array
    {
        return $this->rows;
    }

    /** @return array<string, GenIndexTermData> */
    public function getSubterms(): array
    {
        return $this->subterms;
    }

    public function addRow(GenIndexRowData $row): void
    {
        $this->rows[] = $row;
    }

    public function getOrCreateSubterm(string $key, string $term): self
    {
        return $this->subterms[$key] ??= new self($term);
    }
}
