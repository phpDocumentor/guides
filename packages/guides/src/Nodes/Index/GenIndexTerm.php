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

namespace phpDocumentor\Guides\Nodes\Index;

use phpDocumentor\Guides\Nodes\AbstractNode;

/**
 * All entries collected for a single genindex term, e.g. "configuration".
 *
 * @extends AbstractNode<string>
 */
final class GenIndexTerm extends AbstractNode
{
    /**
     * @param GenIndexRow[]  $rows     flat rows: "see"/"seealso" first, then plain links
     * @param GenIndexTerm[] $subterms one level of nested sub-entries
     */
    public function __construct(
        private readonly string $term,
        private readonly array $rows,
        private readonly array $subterms,
    ) {
        $this->value = $term;
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    /** @return GenIndexRow[] */
    public function getRows(): array
    {
        return $this->rows;
    }

    /** @return GenIndexTerm[] */
    public function getSubterms(): array
    {
        return $this->subterms;
    }

    public function hasSubterms(): bool
    {
        return $this->subterms !== [];
    }
}
