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
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;

/**
 * One rendered row of a genindex term: either a direct link to a section
 * ("link"), or a "see"/"seealso" cross-reference to another term.
 *
 * @extends AbstractNode<ReferenceNode|null>
 */
final class GenIndexRow extends AbstractNode
{
    public function __construct(
        private readonly GenIndexRowKind $kind,
        private readonly bool $main = false,
        private readonly ReferenceNode|null $reference = null,
        private readonly string|null $seeText = null,
    ) {
        $this->value = $reference;
    }

    public function getKind(): GenIndexRowKind
    {
        return $this->kind;
    }

    public function isLink(): bool
    {
        return $this->kind === GenIndexRowKind::Link;
    }

    public function isSee(): bool
    {
        return $this->kind === GenIndexRowKind::See;
    }

    public function isSeeAlso(): bool
    {
        return $this->kind === GenIndexRowKind::SeeAlso;
    }

    public function isMain(): bool
    {
        return $this->main;
    }

    public function getReference(): ReferenceNode|null
    {
        return $this->reference;
    }

    /** The literal term text a "see"/"seealso" row points at. */
    public function getSeeText(): string|null
    {
        return $this->seeText;
    }
}
