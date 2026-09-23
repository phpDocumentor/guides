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

use phpDocumentor\Guides\Nodes\CompoundNode;

/**
 * The genindex content for a page: a term tree collected and populated by
 * IndexCollectorPass, either project-wide (the `:template: genindex` page)
 * or -- when created by GenIndexDirective -- restricted to documents whose
 * path starts with one of $pathPrefixes.
 *
 * At parse time (when created by GenIndexDirective) this node is an empty
 * placeholder; IndexCollectorPass fills in the real terms during compile,
 * once the project-wide term data actually exists.
 *
 * @extends CompoundNode<GenIndexTerm>
 */
final class GenIndexNode extends CompoundNode
{
    /**
     * @param GenIndexTerm[] $terms
     * @param string[] $pathPrefixes empty means unscoped (whole project)
     */
    public function __construct(
        array $terms,
        private readonly array $pathPrefixes = [],
        private readonly bool $showLetterIndex = true,
    ) {
        parent::__construct($terms);
    }

    /** @return GenIndexTerm[] */
    public function getTerms(): array
    {
        return $this->value;
    }

    /** @return string[] */
    public function getPathPrefixes(): array
    {
        return $this->pathPrefixes;
    }

    /**
     * Whether to group terms under an A-Z jumpbox + per-letter headings, or
     * just list them flat. The letter grouping is of little use for a small
     * list, e.g. a single changelog version's worth of terms.
     */
    public function showLetterIndex(): bool
    {
        return $this->showLetterIndex;
    }
}
