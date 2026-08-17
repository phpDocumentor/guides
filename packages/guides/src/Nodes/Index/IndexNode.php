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
 * Wraps the entries collected from one `.. index::` directive occurrence.
 *
 * Invisible in the rendered page body: the AbstractNode value is left an
 * empty array so DefaultNodeRenderer renders it as '' without needing a
 * dedicated renderer. Collected project-wide by IndexCollectorPass to build
 * the genindex page.
 *
 * @extends AbstractNode<array<never>>
 */
final class IndexNode extends AbstractNode
{
    /** @param IndexEntryNode[] $entries */
    public function __construct(private readonly array $entries)
    {
        $this->value = [];
    }

    /** @return IndexEntryNode[] */
    public function getEntries(): array
    {
        return $this->entries;
    }
}
