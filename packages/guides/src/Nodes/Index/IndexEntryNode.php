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
 * One parsed line of a `.. index::` directive, e.g. `single: foo` or `pair: a; b`.
 *
 * @extends AbstractNode<string[]>
 */
final class IndexEntryNode extends AbstractNode
{
    /** @param string[] $parts */
    public function __construct(
        private readonly IndexEntryType $type,
        array $parts,
        private readonly bool $main = false,
    ) {
        $this->value = $parts;
    }

    public function getType(): IndexEntryType
    {
        return $this->type;
    }

    /** @return string[] */
    public function getParts(): array
    {
        return $this->value;
    }

    public function isMain(): bool
    {
        return $this->main;
    }
}
