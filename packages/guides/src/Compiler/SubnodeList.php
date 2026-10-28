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

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Nodes\Node;

final class SubnodeList
{
    /** @param list<CallableAccessor<covariant Node, covariant mixed>> $fields */
    public function __construct(private readonly array $fields)
    {
    }

    public function merge(self $other): self
    {
        return new self([...$this->fields, ...$other->fields]);
    }

    /** @return list<CallableAccessor<covariant Node, covariant mixed>> */
    public function all(): array
    {
        return $this->fields;
    }
}
