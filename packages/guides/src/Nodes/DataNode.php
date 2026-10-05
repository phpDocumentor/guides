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

use function array_merge;
use function array_unique;
use function array_values;
use function implode;

/**
 * A named piece of data attached to another node, rendered as an HTML
 * `data-*` attribute on it.
 *
 * Some of what the compiler learns about a node is not content: the index
 * terms filed under a section are the first case. Rather than each such fact
 * getting a property of its own on the node it describes, and a template
 * change to write it out, it is attached as one of these with
 * {@see AbstractNode::addMetaData()} and written by the `renderDataAttributes`
 * Twig function -- so a search tool reading the rendered page (Pagefind, for
 * one, reads `data-*` attributes) can find it, and a theme or extension can
 * attach its own without touching the node class or the template.
 *
 * Values are kept in the order they were added, each once. A node never holds
 * two of these under the same name: attaching one under a name that is taken
 * adds its values to those already there.
 *
 * @extends AbstractNode<list<string>>
 */
final class DataNode extends AbstractNode
{
    /** @param array<string> $values in any order and with repeats; kept in order, each once */
    public function __construct(private readonly string $name, array $values = [])
    {
        $this->value = array_values(array_unique($values));
    }

    /** The attribute name without its "data-" prefix, e.g. "guides-index-terms". */
    public function getName(): string
    {
        return $this->name;
    }

    /** @return list<string> */
    public function getValues(): array
    {
        return $this->value;
    }

    /** These values, followed by the other's that are not among them yet. */
    public function merge(self $other): self
    {
        return new self($this->name, array_merge($this->value, $other->getValues()));
    }

    /** The values the way the attribute carries them: comma-separated. */
    public function toString(): string
    {
        return implode(',', $this->value);
    }
}
