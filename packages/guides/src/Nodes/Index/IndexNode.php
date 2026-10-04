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
 * A `:name:` on the directive makes the entries a target a `:ref:` can point
 * at. The name is only carried here; it is registered once the compiler knows
 * which section the entries are filed under, and points at that section.
 *
 * @extends AbstractNode<array<never>>
 */
final class IndexNode extends AbstractNode
{
    /**
     * The data a section carries its index terms under, rendered as
     * `data-guides-index-terms="installation,configuration"`.
     */
    public const TERMS_DATA_NAME = 'guides-index-terms';

    /** @param IndexEntryNode[] $entries */
    public function __construct(private readonly array $entries, private readonly string|null $name = null)
    {
        $this->value = [];
    }

    public function getName(): string|null
    {
        return $this->name;
    }

    /** @return IndexEntryNode[] */
    public function getEntries(): array
    {
        return $this->entries;
    }
}
