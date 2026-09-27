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

use phpDocumentor\Guides\Nodes\Index\GenIndexRowKind;

/**
 * One collected genindex row, before it's turned into a rendered
 * {@see \phpDocumentor\Guides\Nodes\Index\GenIndexRow} node: either a direct
 * link to a section, or a "see"/"seealso" cross-reference to another term.
 */
final class GenIndexRowData
{
    public function __construct(
        public readonly GenIndexRowKind $kind,
        public readonly bool $main,
        public readonly string|null $anchor,
        public readonly string|null $title,
        /** The literal term text a "see"/"seealso" row points at. */
        public readonly string|null $seeText,
        /** Path of the document the entry was written in, used for `:scope:` filtering. */
        public readonly string $filePath,
    ) {
    }

    /** A "see"/"seealso" row resolved to point at its target's anchor. */
    public function withAnchor(string|null $anchor): self
    {
        return new self($this->kind, $this->main, $anchor, $this->title, $this->seeText, $this->filePath);
    }
}
