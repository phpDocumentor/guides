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

namespace phpDocumentor\Guides\Nodes\Inline;

use phpDocumentor\Guides\Nodes\AbstractNode;

/** @extends AbstractNode<String> */
abstract class InlineNode extends AbstractNode implements InlineNodeInterface
{
    public function __construct(private readonly string $type, string $value = '')
    {
        $this->value = $value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
